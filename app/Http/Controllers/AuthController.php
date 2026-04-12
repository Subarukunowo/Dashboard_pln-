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
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Callback dari Google
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // 1. Sync User Data
            $user = User::updateOrCreate(
                ['google_id' => $googleUser->getId()],
                [
                    'name'   => $googleUser->getName(),
                    'email'  => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            // 2. Security Check: Akun Aktif?
            if (!$user->is_active) {
                return $this->abortLogin('Akun Anda dinonaktifkan oleh admin.');
            }

            // Login sementara untuk keperluan pengecekan data
            Auth::login($user, true);

            // 3. Device Management
            $token      = $this->getDeviceToken($request);
            $deviceName = $this->getDeviceName($request);
            $existing   = UserDevice::where('user_id', $user->id)->get();
            $known      = $existing->firstWhere('device_token', $token);

            if ($known) {
                return $this->handleKnownDevice($request, $user, $known);
            }

            return $this->handleNewDevice($request, $user, $existing, $token, $deviceName);

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return redirect()->route('login')->with('error', 'Terjadi kesalahan sistem saat login.');
        }
    }

    /**
     * Logic untuk perangkat yang sudah pernah terdaftar
     */
    private function handleKnownDevice($request, $user, $known)
    {
        if ($known->status === 'approved') {
            $known->update([
                'ip_address'   => $request->ip(),
                'last_seen_at' => now(),
            ]);

            ActivityLogger::log('login', 'User login via Google', ['email' => $user->email], $request);

            // Lanjut ke alur 2FA
            return $this->redirectAfterLogin($user);
        }

        // Jika rejected atau pending
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
            return $this->abortLogin('Batas maksimal 2 perangkat aktif tercapai. Hubungi admin.');
        }

        // Buat request baru jika belum ada yang pending
        UserDevice::firstOrCreate(
            ['user_id' => $user->id, 'device_token' => $token],
            [
                'device_name'  => $deviceName,
                'ip_address'   => $request->ip(),
                'status'       => 'pending',
                'requested_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        // Notifikasi Admin
        $this->notifyAdmins($user, $deviceName);

        ActivityLogger::log('device_request', "Request perangkat baru: $deviceName", ['device' => $deviceName], $request);

        return $this->abortLogin('Perangkat baru terdeteksi. Menunggu persetujuan admin.', 'pending');
    }

    /**
     * Tentukan arah redirect setelah login sukses
     */
    private function redirectAfterLogin($user)
    {
        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }

        session(['2fa_verified' => false]);
        return redirect()->route('2fa.verify');
    }

    /**
     * Helper untuk membatalkan login
     */
    private function abortLogin($message, $sessionKey = 'error')
    {
        Auth::logout();
        return redirect()->route('login')->with($sessionKey, $message);
    }

    /**
     * Kirim email ke semua admin
     */
    private function notifyAdmins($user, $deviceName)
    {
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new \App\Mail\DeviceRequestMail($user, $deviceName));
            } catch (\Exception $e) {
                Log::error("Gagal kirim email notifikasi ke admin: " . $admin->email);
            }
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
        return hash('sha256', $request->userAgent() . $request->header('Accept-Language'));
    }

    private function getDeviceName(Request $request): string
    {
        $agent = new \hisorange\BrowserDetect\Parser();
        $agent->setUserAgent($request->userAgent());

        $browser  = $agent->browserName() ?: 'Unknown Browser';
        $os       = $agent->osPlatform() ?: 'Unknown OS';
        $device   = $agent->deviceModel();

        return ($device && $device !== 'unknown') ? "$device — $browser" : "$browser on $os";
    }
}