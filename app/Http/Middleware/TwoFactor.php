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

        if (!$user) return $next($request);

        // Belum setup 2FA — paksa setup dulu
        if (!$user->two_factor_enabled) {
            if ($request->routeIs('2fa.*') || $request->routeIs('logout')) {
                return $next($request);
            }
            return redirect()->route('2fa.setup');
        }

        // Sudah setup tapi belum verify di session ini
        if (!session('2fa_verified')) {
            if ($request->routeIs('2fa.*') || $request->routeIs('logout')) {
                return $next($request);
            }
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}