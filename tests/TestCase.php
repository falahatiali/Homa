<?php

namespace Homa\Tests;

use Homa\HomaServiceProvider;
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
            HomaServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Homa' => \Homa\Facades\Homa::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default configuration
        $app['config']->set('homa.default', 'openai');
        $app['config']->set('homa.providers.openai', [
            'api_key' => 'test-key',
            'api_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-4',
        ]);
    }
}
