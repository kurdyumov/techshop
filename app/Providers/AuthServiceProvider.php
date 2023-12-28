<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Guards\UserGuard;
use App\Guards\AnotherGuard;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::extend('another', function ($app, $name, array $config) {
            $guard = new AnotherGuard(
                $name,
                new AnotherProvider($app),
                $app['session.store'],
                $app['request'],
                null
            );
            // dd($guard);
            return $guard;
        });

        Auth::extend('user', function ($app, $name, array $config) {
            $guard = new UserGuard(
                $name,
                new UserProvider($app),
                $app['session.store'],
                $app['request'],
                null
            );
            // dd($guard);
            return $guard;
        });
    }
}
