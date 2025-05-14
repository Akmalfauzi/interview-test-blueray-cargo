<?php

namespace App\Providers;

use App\Services\Auth\API\V1\LoginService;
use App\Services\Auth\API\V1\RegisterService;
use App\Services\Auth\TokenService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Token Service
        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService();
        });

        // Register Auth Services
        $this->app->singleton(LoginService::class, function ($app) {
            return new LoginService(
                $app->make(TokenService::class)
            );
        });

        $this->app->singleton(RegisterService::class, function ($app) {
            return new RegisterService(
                $app->make(TokenService::class)
            );
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
