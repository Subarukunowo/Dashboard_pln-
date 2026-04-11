<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function setup()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->two_factor_secret = $secret;
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

        $user = Auth::user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->otp);

        if (!$valid) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        $user->two_factor_enabled = true;
        $user->save();

        return redirect()->route('dashboard')->with('success', '2FA berhasil diaktifkan.');
    }

    public function verify()
    {
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

        session(['2fa_verified' => true]);
        return redirect()->intended(route('dashboard'));
    }

    public function disable(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $user = Auth::user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->otp);

        if (!$valid) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->save();

        session()->forget('2fa_verified');
        return redirect()->route('dashboard')->with('success', '2FA dinonaktifkan.');
    }
}