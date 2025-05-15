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
        // 
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
