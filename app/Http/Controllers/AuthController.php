<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate(
                ['google_id' => $googleUser->getId()],
                [
                    'name'   => $googleUser->getName(),
                    'email'  => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            Auth::login($user, true);

            $token      = $this->getDeviceToken($request);
            $deviceName = $this->getDeviceName($request);
            $existing   = UserDevice::where('user_id', $user->id)->get();
            $known      = $existing->firstWhere('device_token', $token);

            if ($known) {
                $known->update([
                    'ip_address'   => $request->ip(),
                    'last_seen_at' => now(),
                ]);
            } else {
                if ($existing->count() >= 2) {
                    Auth::logout();
                    return redirect()->route('login')->with('error',
                        'Akun ini sudah terdaftar di 2 perangkat. Hapus salah satu perangkat terlebih dahulu.'
                    );
                }

                UserDevice::create([
                    'user_id'      => $user->id,
                    'device_token' => $token,
                    'device_name'  => $deviceName,
                    'ip_address'   => $request->ip(),
                    'last_seen_at' => now(),
                ]);
            }

            if (!$user->two_factor_enabled) {
                return redirect()->route('2fa.setup');
            }

            return redirect()->route('2fa.verify');

        } catch (\Exception $e) {
            Log::error('Google Auth Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return redirect()->route('login')->with('error', 'Login gagal, coba lagi.');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    private function getDeviceToken(Request $request): string
    {
        return hash('sha256',
            $request->userAgent() .
            $request->header('Accept-Language') .
            $request->header('Accept-Encoding')
        );
    }

    private function getDeviceName(Request $request): string
    {
        $ua = $request->userAgent();

        $os = match(true) {
            str_contains($ua, 'Windows')   => 'Windows',
            str_contains($ua, 'Macintosh') => 'Mac',
            str_contains($ua, 'Linux')     => 'Linux',
            str_contains($ua, 'Android')   => 'Android',
            str_contains($ua, 'iPhone')    => 'iPhone',
            default                         => 'Unknown OS'
        };

        $browser = match(true) {
            str_contains($ua, 'Edg')     => 'Edge',
            str_contains($ua, 'Chrome')  => 'Chrome',
            str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Safari')  => 'Safari',
            default                       => 'Unknown Browser'
        };

        return "$browser on $os";
    }
}