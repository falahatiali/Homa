<?php

namespace Homa\Tests\Integration;

use Homa\Providers\GroqProvider;
use Homa\Tests\TestCase;
use Mockery;

class GroqProviderTest extends TestCase
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
            'model' => 'openai/gpt-oss-20b',
        ];

        $provider = new GroqProvider($config);

        $this->assertInstanceOf(GroqProvider::class, $provider);
    }

    /** @test */
    public function it_uses_correct_default_model(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GroqProvider($config);

        $this->assertEquals('openai/gpt-oss-20b', $provider->getModel());
    }

    /** @test */
    public function it_can_set_model(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GroqProvider($config);

        $result = $provider->setModel('llama-3.1-70b-versatile');

        $this->assertSame($provider, $result);
        $this->assertEquals('llama-3.1-70b-versatile', $provider->getModel());
    }

    /** @test */
    public function it_can_set_temperature(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GroqProvider($config);

        $result = $provider->setTemperature(0.9);

        $this->assertSame($provider, $result);
        $this->assertEquals(0.9, $provider->getTemperature());
    }

    /** @test */
    public function it_can_set_max_tokens(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GroqProvider($config);

        $result = $provider->setMaxTokens(2000);

        $this->assertSame($provider, $result);
        $this->assertEquals(2000, $provider->getMaxTokens());
    }

    /** @test */
    public function it_validates_configuration(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GroqProvider($config);

        $this->assertTrue($provider->validateConfig());
    }

    /** @test */
    public function it_fails_validation_without_api_key(): void
    {
        $config = ['api_key' => ''];
        $provider = new GroqProvider($config);

        $this->assertFalse($provider->validateConfig());
    }

    /** @test */
    public function it_returns_available_models(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GroqProvider($config);

        $models = $provider->getAvailableModels();

        $this->assertIsArray($models);
        $this->assertContains('openai/gpt-oss-20b', $models);
        $this->assertContains('llama-3.1-70b-versatile', $models);
        $this->assertContains('mixtral-8x7b-32768', $models);
    }

    /** @test */
    public function it_uses_correct_api_url(): void
    {
        $config = [
            'api_key' => 'test-key',
            'api_url' => 'https://api.groq.com/openai/v1',
        ];

        $provider = new GroqProvider($config);

        // The provider should be instantiated without errors
        $this->assertInstanceOf(GroqProvider::class, $provider);
    }

    /** @test */
    public function it_uses_default_api_url_when_not_provided(): void
    {
        $config = ['api_key' => 'test-key'];
        $provider = new GroqProvider($config);

        // Should use default Groq API URL
        $this->assertInstanceOf(GroqProvider::class, $provider);
    }
}
