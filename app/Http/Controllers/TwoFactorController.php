<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function setup()
    {
        $user      = Auth::user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
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
        $request->validate(['otp' => 'required|digits:6']);

        $user      = Auth::user();
        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->two_factor_secret, $request->otp)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        $user->two_factor_enabled = true;
        $user->save();

        session(['2fa_verified' => true]);

        ActivityLogger::log('2fa_enabled', 'User mengaktifkan 2FA', null, $request);

        $route = $user->hasRole('admin') ? 'admin.dashboard' : 'dashboard';
        return redirect()->route($route)->with('success', '2FA berhasil diaktifkan.');
    }

    public function verify()
    {
        // Kalau sudah verified di session, langsung redirect
        if (session('2fa_verified')) {
            $user  = Auth::user();
            $route = $user->hasRole('admin') ? 'admin.dashboard' : 'dashboard';
            return redirect()->route($route);
        }

        return view('auth.two-factor-verify');
    }

  public function check(Request $request)
{
    $request->validate(['otp' => 'required|digits:6']);

    $user = Auth::user();
    $google2fa = new Google2FA();

    $valid = $google2fa->verifyKey($user->two_factor_secret, $request->otp);

    if (!$valid) {
        return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kedaluwarsa.']);
    }

    // Set true secara eksplisit
    session(['2fa_verified' => true]);

    $redirectTo = $user->hasRole('admin') ? 'admin.dashboard' : 'dashboard';
    return redirect()->route($redirectTo);
}

    public function disable(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $user      = Auth::user();
        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->two_factor_secret, $request->otp)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret  = null;
        $user->save();

        session()->forget('2fa_verified');

        ActivityLogger::log('2fa_disabled', 'User menonaktifkan 2FA', null, $request);

        $route = $user->hasRole('admin') ? 'admin.dashboard' : 'dashboard';
        return redirect()->route($route)->with('success', '2FA dinonaktifkan.');
    }
}