<?php

namespace App\Http\Controllers;

use App\Mail\DeviceRequestMail;
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

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->avatar_original ?? $googleUser->getAvatar(),
                ]);
            } else {
                $user = User::create([
                    'google_id' => $googleUser->getId(),
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'avatar'    => $googleUser->avatar_original ?? $googleUser->getAvatar(),
                    'password'  => bcrypt(str()->random(24)),
                    'is_active' => true,
                ]);
            }

            if (!$user->is_active) {
                return $this->abortLogin('Akun Anda dinonaktifkan oleh admin.');
            }

            Auth::login($user, true);

            // Admin bypass device check
            if ($user->hasRole('admin')) {
                ActivityLogger::log('login', 'Admin login via Google', ['email' => $user->email], $request);
                return $this->redirectAfterLogin($user);
            }

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
                    return $this->redirectAfterLogin($user);
                }

                $message = $known->status === 'rejected'
                    ? 'Perangkat ditolak: ' . ($known->reject_reason ?? 'Akses tidak diizinkan.')
                    : 'Perangkat ini masih menunggu persetujuan admin.';

                return $this->abortLogin($message, $known->status === 'pending' ? 'pending' : 'error');
            }

            return $this->handleNewDevice($request, $user, $existing, $token, $deviceName);

        } catch (\Exception $e) {
            Log::error('Google Auth Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);
            return redirect()->route('login')->with('error', 'Terjadi kesalahan sistem saat login.');
        }
    }

    /**
     * Registrasi perangkat baru + kirim email ke semua admin
     */
    private function handleNewDevice(Request $request, User $user, $existing, string $token, string $deviceName)
    {
        if ($existing->where('status', 'approved')->count() >= 2) {
            return $this->abortLogin('Batas maksimal 2 perangkat aktif tercapai. Silahkan hubungi admin.');
        }

        $device = UserDevice::updateOrCreate(
            ['user_id' => $user->id, 'device_token' => $token],
            [
                'device_name'  => $deviceName,
                'ip_address'   => $request->ip(),
                'status'       => 'pending',
                'requested_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        $this->notifyAdmins($user, $device);

        ActivityLogger::log('device_request', "Request perangkat baru: $deviceName", ['device' => $deviceName], $request);

        return $this->abortLogin('Perangkat baru terdeteksi. Silahkan tunggu persetujuan admin.', 'pending');
    }

    /**
     * Kirim email notifikasi ke semua admin
     */
    private function notifyAdmins(User $user, UserDevice $device): void
    {
        try {
            $admins = User::role('admin')->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(new DeviceRequestMail($user, $device));
            }
        } catch (\Exception $e) {
            Log::error('Gagal kirim email notifikasi ke admin', [
                'error'  => $e->getMessage(),
                'user'   => $user->email,
                'device' => $device->device_name,
            ]);
        }
    }

    /**
     * Redirect setelah login sukses
     */
    private function redirectAfterLogin(User $user)
    {
        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }

        session()->forget('2fa_verified');
        return redirect()->route('2fa.verify');
    }

    /**
     * Batalkan login & bersihkan session
     */
    private function abortLogin(string $message, string $sessionKey = 'error')
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login')->with($sessionKey, $message);
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
        return hash('sha256',
            $request->userAgent() .
            $request->header('Accept-Language')
        );
    }

    private function getDeviceName(Request $request): string
    {
        if (\Browser::isMobile() || \Browser::isTablet()) {
            $vendor   = \Browser::deviceFamily();
            $model    = \Browser::deviceModel();
            $fullName = trim($vendor . ' ' . $model);

            if ($fullName && !str_contains(strtolower($fullName), 'unknown')) {
                return $fullName;
            }

            return \Browser::platformName() . ' Mobile Device';
        }

        $os      = \Browser::platformName() ?: 'Windows/macOS';
        $browser = \Browser::browserName() ?: 'Browser';

        return "Laptop/PC ($os) — $browser";
    }
}