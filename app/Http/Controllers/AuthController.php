<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDevice;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect ke Google Login
     */
    public function redirectToGoogle()
    {
       return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Callback dari Google
     */
public function handleGoogleCallback(Request $request)
{
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name'   => $googleUser->getName(),
                'email'  => $googleUser->getEmail(),
                'avatar' => $googleUser->avatar_original ?? $googleUser->getAvatar(),
            ]
        );

        if (!$user->is_active) {
            return $this->abortLogin('Akun Anda dinonaktifkan oleh admin.');
        }

        Auth::login($user, true);

        // 1. CEK DEVICE DULU
        $token      = $this->getDeviceToken($request);
        $deviceName = $this->getDeviceName($request);
        $existing   = UserDevice::where('user_id', $user->id)->get();
        $known      = $existing->firstWhere('device_token', $token);

        if ($known) {
            if ($known->status === 'approved') {
                $known->update([
                    'ip_address'   => $request->ip(),
                    'last_seen_at' => now(),
                ]);
                ActivityLogger::log('login', 'User login via Google', ['email' => $user->email], $request);
                // 2. BARU CEK 2FA
                return $this->redirectAfterLogin($user);
            }

            $message = $known->status === 'rejected'
                ? "Perangkat ditolak: " . ($known->reject_reason ?? 'Akses tidak diizinkan.')
                : "Perangkat ini masih menunggu persetujuan admin.";

            return $this->abortLogin($message, $known->status === 'pending' ? 'pending' : 'error');
        }

        // 3. DEVICE BARU
        return $this->handleNewDevice($request, $user, $existing, $token, $deviceName);

  } catch (\Exception $e) {
    Log::error('Google Auth Error', [
        'message' => $e->getMessage(),
        'class'   => get_class($e),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);
    
    // Tampilkan langsung di browser untuk debug
    dd([
        'message' => $e->getMessage(),
        'class'   => get_class($e),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);
}
}
    /**
     * Logic untuk perangkat yang sudah pernah terdaftar
     */
    private function handleKnownDevice($request, $user, $known)
    {
        if ($known->status === 'approved') {
            // Login user secara resmi
            Auth::login($user, true);

            $known->update([
                'ip_address'   => $request->ip(),
                'last_seen_at' => now(),
            ]);

            ActivityLogger::log('login', 'User login via Google', ['email' => $user->email], $request);

            return $this->redirectAfterLogin($user);
        }

        // Jika rejected atau pending, tendang keluar
        $message = ($known->status === 'rejected') 
            ? "Perangkat ditolak: " . ($known->reject_reason ?? 'Akses tidak diizinkan.')
            : "Perangkat ini masih menunggu persetujuan admin.";
            
        return $this->abortLogin($message, $known->status === 'pending' ? 'pending' : 'error');
    }

    /**
     * Logic untuk registrasi perangkat baru
     */
    private function handleNewDevice($request, $user, $existing, $token, $deviceName)
    {
        // Limit 2 perangkat approved
        if ($existing->where('status', 'approved')->count() >= 2) {
            return $this->abortLogin('Batas maksimal 2 perangkat aktif tercapai. Silahkan hubungi admin.');
        }

        // Simpan data perangkat baru dengan status pending
        UserDevice::updateOrCreate(
            ['user_id' => $user->id, 'device_token' => $token],
            [
                'device_name'  => $deviceName,
                'ip_address'   => $request->ip(),
                'status'       => 'pending',
                'requested_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        // Kirim Notifikasi Email ke Admin
        //$this->notifyAdmins($user, $deviceName);

        ActivityLogger::log('device_request', "Request perangkat baru: $deviceName", ['device' => $deviceName], $request);

        return $this->abortLogin('Perangkat baru terdeteksi. Silahkan tunggu persetujuan admin.', 'pending');
    }

    /**
     * Tentukan arah redirect setelah login sukses
     */
 private function redirectAfterLogin($user)
{
    if (!$user->two_factor_enabled) {
        return redirect()->route('2fa.setup');
    }

    // Hapus session 2fa_verified agar middleware memblokir
    session()->forget('2fa_verified');
    return redirect()->route('2fa.verify');
}  /**
     * Helper untuk membatalkan login & membersihkan session
     */
    private function abortLogin($message, $sessionKey = 'error')
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('login')->with($sessionKey, $message);
    }

    /**
     * Kirim email ke semua admin
     */
    private function notifyAdmins($user, $deviceName)
    {
        try {
            // Ambil admin menggunakan Spatie Role
            $admins = User::role('admin')->get();

            if ($admins->isNotEmpty()) {
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new \App\Mail\DeviceRequestMail($user, $deviceName));
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal kirim email notifikasi: " . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('logout', 'User logout', null, $request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function getDeviceToken(Request $request): string
{
    // Menggunakan 3 parameter agar fingerprint lebih unik & konsisten
    return hash('sha256', 
        $request->userAgent() . 
        $request->header('Accept-Language') . 
        $request->header('Accept-Encoding')
    );
}

 private function getDeviceName(Request $request): string
{
    // Cek apakah ini perangkat mobile (HP/Tablet)
    if (\Browser::isMobile() || \Browser::isTablet()) {
        // Ambil Merek (Vendor) dan Model spesifik
        $vendor = \Browser::deviceFamily(); 
        $model  = \Browser::deviceModel();  

        $fullName = trim($vendor . ' ' . $model);

        // Jika berhasil menangkap nama spesifik, return itu
        if ($fullName && !str_contains(strtolower($fullName), 'unknown')) {
            return $fullName;
        }
        
        // Fallback jika merek tidak terbaca jelas
        return \Browser::platformName() . ' Mobile Device';
    }

    // Untuk Laptop/PC
    // Gunakan platformName() bukan osPlatform()
    $os      = \Browser::platformName() ?: 'Windows/macOS';
    $browser = \Browser::browserName() ?: 'Browser';
    
    return "Laptop/PC ($os) — $browser";
}
}