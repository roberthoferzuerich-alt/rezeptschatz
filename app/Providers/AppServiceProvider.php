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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auf Produktionsservern (z. B. Raspberry Pi) erzwingen wir die feste Domain und Port
        // Lokal (Laragon) übernimmt Laravel dynamisch die Domain und den Port des Browsers
        if (!app()->environment('local')) {
            if ($appUrl = config('app.url')) {
                \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
                if (str_starts_with($appUrl, 'https://')) {
                    \Illuminate\Support\Facades\URL::forceScheme('https');
                }
            }
        }
    }
}
