<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use App\Repositories\Contracts\StatsRepositoryInterface;
use App\Repositories\StatsRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StatsRepositoryInterface::class, fn ($app) => new StatsRepository());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Response::macro('jsonFloat', function($data) {
            return response()->json($data, 200, [], JSON_PRESERVE_ZERO_FRACTION);
        });

        $this->configureRateLimiting();
    }

    /**
     * Definisi rate limiter untuk auth, 2FA, chatbot, dan OAuth.
     */
    private function configureRateLimiting(): void
    {
        // Google OAuth: max 10 kali/menit per IP
        RateLimiter::for('google-oauth', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // 2FA verify/enable/disable: max 5 kali/menit per user/IP
        RateLimiter::for('2fa-verify', function ($request) {
            return Limit::perMinute(5)->by(
                optional($request->user())->id ?? $request->ip()
            );
        });

        // Logout: max 10 kali/menit (cegah session flooding)
        RateLimiter::for('logout', function ($request) {
            return Limit::perMinute(10)->by(
                optional($request->user())->id ?? $request->ip()
            );
        });

        // Chatbot: max 20 kali/menit per user/IP
        RateLimiter::for('chatbot', function ($request) {
            return Limit::perMinute(20)->by(
                optional($request->user())->id ?? $request->ip()
            );
        });
    }
}
