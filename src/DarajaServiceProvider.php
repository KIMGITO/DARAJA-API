<?php

namespace Codenson\Daraja;

use Codenson\Daraja\Console\Commands\DarajaInstallCommand;
use Codenson\Daraja\Daraja;
use Codenson\Daraja\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class DarajaServiceProvider extends ServiceProvider
{
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

    public function boot(): void
    {
        // Load migrations directly without publishing
        if ($this->app->runningInConsole()) {
            // Register commands
            $this->commands([
                DarajaInstallCommand::class,
            ]);
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            // Publish config only
            $this->publishes([
                __DIR__ . '/Config/daraja.php' => config_path('daraja.php'),
            ], 'daraja-config');
        }
    }
}