<?php

namespace Homa;

use Illuminate\Support\ServiceProvider;
use Homa\Contracts\AIProviderInterface;
use Homa\Manager\HomaManager;

class HomaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/homa.php',
            'homa'
        );

        // Register the main Homa Manager as singleton
        $this->app->singleton('homa', function ($app) {
            return new HomaManager($app);
        });

        // Bind the interface to the manager
        $this->app->bind(AIProviderInterface::class, function ($app) {
            return $app->make('homa');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/homa.php' => config_path('homa.php'),
        ], 'homa-config');
    }
}

