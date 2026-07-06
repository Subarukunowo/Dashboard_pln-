<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Wajib agar Sanctum mengautentikasi request API via session/cookie.
        // statefulApi() saja tidak cukup — middleware ini butuh StartSession,
        // EncryptCookies, dan VerifyCsrfToken juga berjalan di grup "api"
        // supaya cookie session benar-benar terbaca jadi session aktif.
        $middleware->statefulApi();

        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->alias([
            '2fa'          => \App\Http\Middleware\TwoFactor::class,
            'admin'        => \App\Http\Middleware\AdminOnly::class,
            'internal.api' => \App\Http\Middleware\InternalApiOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withCommands([
        \App\Console\Commands\CacheUserAvatars::class,
    ])
    ->create();