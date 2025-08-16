<?php

namespace Kristiansnts\FilamentApiLogin;

use Kristiansnts\FilamentApiLogin\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class FilamentApiLoginServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-api-login.php', 'filament-api-login');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the custom authentication guard
        Auth::extend('external_session', function ($app, $name, $config) {
            return new SessionGuard(
                $app['request'],
                $app['session.store']
            );
        });

        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/filament-api-login.php' => config_path('filament-api-login.php'),
        ], 'filament-api-login-config');

        // Optionally auto-publish the config file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-api-login.php' => config_path('filament-api-login.php'),
            ], 'config');
        }
    }
}