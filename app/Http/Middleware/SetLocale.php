<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale'));
        app()->setLocale($locale);

        // DEBUG SEMENTARA — hapus setelah fix
        \Log::debug('SetLocale', [
            'session_locale' => session('locale'),
            'applied'        => $locale,
            'url'            => $request->url(),
        ]);

        return $next($request);
    }
}