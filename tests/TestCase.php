<?php

namespace LaravelSage\Tests;

use LaravelSage\SageServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SageServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Sage' => \LaravelSage\Facades\Sage::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default configuration
        $app['config']->set('sage.default', 'openai');
        $app['config']->set('sage.providers.openai', [
            'api_key' => 'test-key',
            'api_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-4',
        ]);
    }
}
