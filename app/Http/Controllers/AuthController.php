<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
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

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login gagal, coba lagi.');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}