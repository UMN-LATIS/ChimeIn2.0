<?php

namespace App\Providers;

use Firebase\JWT\JWT;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Packback\Lti1p3\Interfaces\ICache;
use Packback\Lti1p3\Interfaces\ICookie;
use Packback\Lti1p3\Interfaces\IDatabase;
use Packback\Lti1p3\Interfaces\ILtiServiceConnector as LtiServiceConnector;
use GuzzleHttp\Client;

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
        $this->app->bind(LtiServiceConnector::class, function () {
            return new LtiServiceConnector(app(ICache::class), new Client([
                'timeout' => 30,
            ]));
        });

    }
}
