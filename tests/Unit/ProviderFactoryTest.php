<?php

namespace Homa\Tests\Unit;

use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\ConfigurationException;
use Homa\Factories\ProviderFactory;
use Homa\Providers\AnthropicProvider;
use Homa\Providers\OpenAIProvider;
use Homa\Tests\TestCase;
use InvalidArgumentException;

class ProviderFactoryTest extends TestCase
{
    protected ProviderFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ProviderFactory;
    }

    /** @test */
    public function it_can_create_openai_provider(): void
    {
        $config = [
            'api_key' => 'test-key',
            'model' => 'gpt-4',
        ];

        $provider = $this->factory->make('openai', $config);

        $this->assertInstanceOf(OpenAIProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    /** @test */
    public function it_can_create_anthropic_provider(): void
    {
        $config = [
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ];

        $provider = $this->factory->make('anthropic', $config);

        $this->assertInstanceOf(AnthropicProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    /** @test */
    public function it_throws_exception_for_unsupported_provider(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provider [unsupported] is not supported');

        $this->factory->make('unsupported', ['api_key' => 'test']);
    }

    /** @test */
    public function it_throws_exception_when_api_key_is_missing(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('API key is required');

        $this->factory->make('openai', ['model' => 'gpt-4']);
    }

    /** @test */
    public function it_can_extend_with_custom_provider(): void
    {
        $customProvider = new class implements AIProviderInterface
        {
            public function __construct(array $config = []) {}

            public function sendMessage(array $messages, array $options = []): \Homa\Response\AIResponse
            {
                return new \Homa\Response\AIResponse('test');
            }

            public function setModel(string $model): self
            {
                return $this;
            }

            public function setTemperature(float $temperature): self
            {
                return $this;
            }

            public function setMaxTokens(int $maxTokens): self
            {
                return $this;
            }

            public function validateConfig(): bool
            {
                return true;
            }
        };

        $this->factory->extend('custom', get_class($customProvider));

        $provider = $this->factory->make('custom', ['api_key' => 'test']);

        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    /** @test */
    public function it_throws_exception_when_extending_with_invalid_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provider class must implement AIProviderInterface');

        $this->factory->extend('invalid', \stdClass::class);
    }

    /** @test */
    public function it_returns_available_providers(): void
    {
        $providers = $this->factory->getAvailableProviders();

        $this->assertIsArray($providers);
        $this->assertContains('openai', $providers);
        $this->assertContains('anthropic', $providers);
    }

    /** @test */
    public function it_loads_config_from_laravel_config_when_not_provided(): void
    {
        config([
            'homa.providers.openai' => [
                'api_key' => 'config-test-key',
                'model' => 'gpt-4',
            ],
        ]);

        $provider = $this->factory->make('openai');

        $this->assertInstanceOf(OpenAIProvider::class, $provider);
    }

    /** @test */
    public function it_throws_exception_when_provider_not_in_config(): void
    {
        config(['homa.providers' => []]);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Configuration for provider [openai] not found');

        $this->factory->make('openai');
    }
}
