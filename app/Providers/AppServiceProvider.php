<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
      // Daftarkan sebagai singleton
        $this->app->singleton(Era1997_2004Transformer::class, function ($app) {
            return new Era1997_2004Transformer();
            });
    }
    

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
