<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\StatsRepositoryInterface;
use App\Repositories\StatsRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
      // Daftarkan sebagai singleton
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
}
}
