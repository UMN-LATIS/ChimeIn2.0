<?php

namespace App\Providers;

use Firebase\JWT\JWT;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Packback\Lti1p3\Interfaces\Cache;
use Packback\Lti1p3\Interfaces\Cookie;
use Packback\Lti1p3\Interfaces\Database;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        JWT::$leeway = 5;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Cache::class, LTI13Cache::class);
        $this->app->bind(Cookie::class, LTI13Cookie::class);
        $this->app->bind(Database::class, LTI13Database::class);

    }
}
