<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Tambahkan baris ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tambahkan 3 baris ini: Jika aplikasi diakses selain dari localhost, paksa pakai HTTPS
        if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') == 'https') {
            URL::forceScheme('https');
        }
    }
}