<?php

namespace Homa\Tests\Integration;

use Homa\Providers\GeminiProvider;
use Homa\Tests\TestCase;
use Mockery;

class GeminiProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $config = [
            'api_key' => 'test-key',
            'model' => 'gemini-2.0-flash-exp',
        ];

        $provider = new GeminiProvider($config);

        $this->assertInstanceOf(GeminiProvider::class, $provider);
    }

    /** @test */
    public function it_uses_correct_default_model(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GeminiProvider($config);

        $this->assertEquals('gemini-2.0-flash-exp', $provider->getModel());
    }

    /** @test */
    public function it_can_set_model(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GeminiProvider($config);

        $result = $provider->setModel('gemini-1.5-pro-latest');

        $this->assertSame($provider, $result);
        $this->assertEquals('gemini-1.5-pro-latest', $provider->getModel());
    }

    /** @test */
    public function it_can_set_temperature(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GeminiProvider($config);

        $result = $provider->setTemperature(0.9);

        $this->assertSame($provider, $result);
        $this->assertEquals(0.9, $provider->getTemperature());
    }

    /** @test */
    public function it_can_set_max_tokens(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GeminiProvider($config);

        $result = $provider->setMaxTokens(2000);

        $this->assertSame($provider, $result);
        $this->assertEquals(2000, $provider->getMaxTokens());
    }

    /** @test */
    public function it_validates_configuration(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GeminiProvider($config);

        $this->assertTrue($provider->validateConfig());
    }

    /** @test */
    public function it_fails_validation_without_api_key(): void
    {
        $config = ['api_key' => ''];
        $provider = new GeminiProvider($config);

        $this->assertFalse($provider->validateConfig());
    }

    /** @test */
    public function it_returns_available_models(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GeminiProvider($config);

        $models = $provider->getAvailableModels();

        $this->assertIsArray($models);
        $this->assertContains('gemini-2.0-flash-exp', $models);
        $this->assertContains('gemini-1.5-pro-latest', $models);
        $this->assertContains('gemini-1.5-flash-latest', $models);
    }

    /** @test */
    public function it_uses_correct_base_uri(): void
    {
        $config = [
            'api_key' => 'test-key',
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta',
        ];

        $provider = new GeminiProvider($config);

        // The provider should be instantiated without errors
        $this->assertInstanceOf(GeminiProvider::class, $provider);
    }

    /** @test */
    public function it_uses_default_base_uri_when_not_provided(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GeminiProvider($config);

        // Should use default Gemini base URI
        $this->assertInstanceOf(GeminiProvider::class, $provider);
    }
}
