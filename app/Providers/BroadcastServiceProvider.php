<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        // The PowerPoint add-in has no session cookie, so it authorizes channels with
        // its bearer token against a parallel endpoint using the same callbacks below.
        Broadcast::routes([
            'prefix' => 'api/office',
            'as' => 'office.broadcasting.auth',
            'middleware' => ['auth:sanctum'],
        ]);

        require base_path('routes/channels.php');
    }
}
