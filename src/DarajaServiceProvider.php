<?php

namespace Codenson\Daraja;

use Illuminate\Support\ServiceProvider;
use Codenson\Daraja\Services\AuthService;

class DarajaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/daraja.php', 'daraja');

        $this->app->singleton('daraja', function ($app) {
            return new Daraja($app['config']['daraja']);
        });

        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService($app['config']['daraja']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/daraja.php' => config_path('daraja.php'),
            ], 'daraja-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'daraja-migrations');
        }

        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}