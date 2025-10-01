<?php

namespace Homa;

use Homa\Contracts\AIProviderInterface;
use Homa\Factories\ProviderFactory;
use Homa\Manager\HomaManager;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel Service Provider for Homa package.
 *
 * Implements Dependency Injection Container registration.
 */
class HomaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/homa.php',
            'homa'
        );

        // Register ProviderFactory as singleton
        $this->app->singleton(ProviderFactory::class, function ($app) {
            return new ProviderFactory;
        });

        // Register the main Homa Manager as singleton with dependency injection
        $this->app->singleton('homa', function ($app) {
            return new HomaManager(
                $app->make(ProviderFactory::class)
            );
        });

        // Bind the interface to the manager (Dependency Inversion Principle)
        $this->app->bind(AIProviderInterface::class, function ($app) {
            return $app->make('homa')->provider(config('homa.default'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/homa.php' => config_path('homa.php'),
        ], 'homa-config');
    }
}
