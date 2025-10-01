<?php

namespace Homa\Factories;

use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\ConfigurationException;
use Homa\Providers\AnthropicProvider;
use Homa\Providers\GrokProvider;
use Homa\Providers\OpenAIProvider;
use InvalidArgumentException;

/**
 * Factory for creating AI provider instances.
 *
 * Uses Factory Pattern to encapsulate provider creation logic.
 * Follows Open/Closed Principle - easy to extend with new providers.
 */
class ProviderFactory
{
    /**
     * Registry of available providers.
     */
    protected array $providers = [
        'openai' => OpenAIProvider::class,
        'anthropic' => AnthropicProvider::class,
        'grok' => GrokProvider::class,
    ];

    /**
     * Create a provider instance.
     *
     * @throws ConfigurationException|InvalidArgumentException
     */
    public function make(string $provider, ?array $config = null): AIProviderInterface
    {
        if (! isset($this->providers[$provider])) {
            throw new InvalidArgumentException(
                "Provider [{$provider}] is not supported. Available providers: ".
                implode(', ', array_keys($this->providers))
            );
        }

        $config = $config ?? $this->getProviderConfig($provider);

        if (empty($config['api_key'])) {
            throw new ConfigurationException(
                "API key is required for provider [{$provider}]. ".
                'Please set it in your configuration or environment variables.'
            );
        }

        $providerClass = $this->providers[$provider];

        return new $providerClass($config);
    }

    /**
     * Register a custom provider.
     *
     * Allows extending the factory with custom providers (Open/Closed Principle).
     */
    public function extend(string $name, string $providerClass): self
    {
        if (! is_subclass_of($providerClass, AIProviderInterface::class)) {
            throw new InvalidArgumentException(
                'Provider class must implement AIProviderInterface.'
            );
        }

        $this->providers[$name] = $providerClass;

        return $this;
    }

    /**
     * Get provider configuration from Laravel config.
     */
    protected function getProviderConfig(string $provider): array
    {
        $config = config("homa.providers.{$provider}");

        if (! $config) {
            throw new ConfigurationException(
                "Configuration for provider [{$provider}] not found."
            );
        }

        return $config;
    }

    /**
     * Get all registered providers.
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }
}
