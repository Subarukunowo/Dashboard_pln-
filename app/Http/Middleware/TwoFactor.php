<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        $shouldBypass = !$user
            || $request->routeIs('2fa.*')
            || $request->routeIs('logout');

        if ($shouldBypass) {
            return $next($request);
        }

        $redirectRoute = $this->resolveRedirectRoute($user);

        if ($redirectRoute) {
            return redirect()->route($redirectRoute);
        }

        return $next($request);
    }

    private function resolveRedirectRoute($user): ?string
    {
        if (!$user->two_factor_enabled) {
            return '2fa.setup';
        }

        // Gunakan === true agar false dan null keduanya diblokir
        if (session('2fa_verified') !== true) {
            return '2fa.verify';
        }

        return null;
    }
}