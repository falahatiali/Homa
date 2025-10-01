<?php

namespace LaravelSage;

use Illuminate\Support\ServiceProvider;
use LaravelSage\Contracts\AIProviderInterface;
use LaravelSage\Manager\SageManager;

class SageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sage.php',
            'sage'
        );

        // Register the main Sage Manager as singleton
        $this->app->singleton('sage', function ($app) {
            return new SageManager($app);
        });

        // Bind the interface to the manager
        $this->app->bind(AIProviderInterface::class, function ($app) {
            return $app->make('sage');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/sage.php' => config_path('sage.php'),
        ], 'sage-config');
    }
}

