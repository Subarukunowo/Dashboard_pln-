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

    if ($request->routeIs('2fa.*') || $request->routeIs('logout')) {
        return $next($request);
    }

    if (!$user->two_factor_enabled) {
        return redirect()->route('2fa.setup');
    }

    // Gunakan === true agar false dan null keduanya diblokir
    if (session('2fa_verified') !== true) {
        return redirect()->route('2fa.verify');
    }

    return $next($request);
}
}