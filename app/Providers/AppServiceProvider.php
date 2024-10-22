<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WialonService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(WialonService::class, function ($app) {
            return new WialonService();
        });

        $this->app->singleton('wialon', function ($app) {
            return new WialonService();
        });
        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
