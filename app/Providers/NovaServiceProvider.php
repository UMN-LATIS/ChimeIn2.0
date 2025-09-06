<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Illuminate\Http\Request;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Nova::userTimezone(function (Request $request) {
            return $request->user()?->timezone ?? 'America/Chicago';
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->register();
    }

    /**
     * Configure the Nova authorization services.
     * This overides the default authorization method in
     * NovaApplicationServiceProvider so that the gate applies
     * in local environments as well.
     *
     * @return void
     */
    protected function authorization() {
        $this->gate();

        Nova::auth(function ($request) {
        return Gate::check('viewNova', [$request->user()]);
        });
    }



    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return  $user->global_admin;
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [];
    }

      /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        parent::register();
    }
}
