<?php

namespace Codenson\Daraja;

use Illuminate\Support\ServiceProvider;
use Codenson\Daraja\Services\AuthService;

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
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/Config/daraja.php' => config_path('daraja.php'),
            ], 'daraja-config');

            // Publish migrations
            if (is_dir(__DIR__ . '/../database/migrations')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations' => database_path('migrations'),
                ], 'daraja-migrations');
            }
        }
    }
}