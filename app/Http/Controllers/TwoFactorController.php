<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
    }

    public function setup()
    {
        $user = Auth::user();

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $this->google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        return view('auth.two-factor-setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret'    => $user->two_factor_secret,
        ]);
    }

    public function enable(Request $request)
    {
        $user = $this->verifyOtpOrFail($request);

        $user->two_factor_enabled = true;
        $user->save();

        session(['2fa_verified' => true]);

        ActivityLogger::log('2fa_enabled', 'User mengaktifkan 2FA', null, $request);

        return redirect()->route($this->dashboardRoute($user))
            ->with('success', '2FA berhasil diaktifkan.');
    }

    public function verify()
    {
        if (session('2fa_verified')) {
            return redirect()->route($this->dashboardRoute(Auth::user()));
        }

        return view('auth.two-factor-verify');
    }

    public function check(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $user    = Auth::user();
        $lockKey = '2fa_attempts_' . $user->id;

        $attempts = cache()->get($lockKey, 0);

        if ($attempts >= 5) {
            $ttl   = cache()->getStore()->connection()->ttl(cache()->getPrefix() . $lockKey);
            $menit = max(1, (int) ceil($ttl / 60));
            return back()->withErrors(['otp' => "Terlalu banyak percobaan. Coba lagi dalam {$menit} menit."]);
        }

        if (!$this->google2fa->verifyKey($user->two_factor_secret, $request->otp)) {
            cache()->put($lockKey, $attempts + 1, now()->addMinutes(15));
            $sisa = 4 - $attempts;
            return back()->withErrors(['otp' => "Kode OTP salah. {$sisa} percobaan tersisa."]);
        }

        cache()->forget($lockKey);
        session(['2fa_verified' => true]);

        ActivityLogger::log('2fa_verified', 'User berhasil verifikasi 2FA', null, $request);

        return redirect()->route($this->dashboardRoute($user));
    }

    public function disable(Request $request)
    {
        $user = $this->verifyOtpOrFail($request);

        $user->two_factor_enabled = false;
        $user->two_factor_secret  = null;
        $user->save();

        session()->forget('2fa_verified');

        ActivityLogger::log('2fa_disabled', 'User menonaktifkan 2FA', null, $request);

        return redirect()->route($this->dashboardRoute($user))
            ->with('success', '2FA dinonaktifkan.');
    }

    /**
     * Validasi OTP, kembalikan user kalau valid, abort dengan redirect kalau tidak.
     */
    protected function verifyOtpOrFail(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $user = Auth::user();

        if (!$this->google2fa->verifyKey($user->two_factor_secret, $request->otp)) {
            abort(back()->withErrors(['otp' => 'Kode OTP tidak valid.']));
        }

        return $user;
    }

    protected function dashboardRoute($user): string
    {
        return $user->hasRole('admin') ? 'admin.dashboard' : 'dashboard';
    }
}