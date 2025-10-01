<?php

namespace Homa\Manager;

use Homa\Contracts\AIProviderInterface;
use Homa\Conversation\Conversation;
use Homa\Factories\ProviderFactory;
use Homa\Response\AIResponse;

/**
 * Main manager for Homa AI package.
 *
 * Implements Facade Pattern for simple API.
 * Uses Dependency Injection for testability.
 * Follows Single Responsibility Principle.
 */
class HomaManager
{
    /**
     * The active provider instance.
     */
    protected ?AIProviderInterface $provider = null;

    /**
     * Custom configuration for this instance.
     */
    protected array $config = [];

    /**
     * Create a new Homa Manager instance.
     *
     * @param  ProviderFactory  $factory  Injected factory (Dependency Inversion)
     */
    public function __construct(
        protected ProviderFactory $factory
    ) {
    }

    /**
     * Set the AI provider.
     *
     * @return  $this
     */
    public function provider(string $provider): self
    {
        $this->provider = $this->factory->make($provider);

        return $this;
    }

    /**
     * Set the model to use.
     *
     * @return  $this
     */
    public function model(string $model): self
    {
        $this->config['model'] = $model;
        $this->getProvider()->setModel($model);

        return $this;
    }

    /**
     * Set the temperature.
     *
     * @return  $this
     */
    public function temperature(float $temperature): self
    {
        $this->config['temperature'] = $temperature;
        $this->getProvider()->setTemperature($temperature);

        return $this;
    }

    /**
     * Set the max tokens.
     *
     * @return  $this
     */
    public function maxTokens(int $maxTokens): self
    {
        $this->config['max_tokens'] = $maxTokens;
        $this->getProvider()->setMaxTokens($maxTokens);

        return $this;
    }

    /**
     * Set the system prompt.
     *
     * @return  $this
     */
    public function systemPrompt(string $prompt): self
    {
        $this->config['system_prompt'] = $prompt;

        return $this;
    }

    /**
     * Ask a simple question.
     */
    public function ask(string $question): AIResponse
    {
        $messages = [];

        // Add system prompt if configured
        if (isset($this->config['system_prompt'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->config['system_prompt'],
            ];
        } elseif ($systemPrompt = config('homa.system_prompt')) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        return $this->getProvider()->sendMessage($messages, $this->config);
    }

    /**
     * Send a chat message with full control.
     */
    public function chat(string|array $messages): AIResponse
    {
        if (is_string($messages)) {
            $messages = [
                ['role' => 'user', 'content' => $messages],
            ];
        }

        return $this->getProvider()->sendMessage($messages, $this->config);
    }

    /**
     * Start a new conversation.
     */
    public function startConversation(): Conversation
    {
        return new Conversation($this->getProvider(), $this->config);
    }

    /**
     * Get the active provider instance.
     *
     * Lazy loading pattern - creates provider only when needed.
     */
    protected function getProvider(): AIProviderInterface
    {
        if (! $this->provider) {
            $defaultProvider = config('homa.default', 'openai');
            $this->provider = $this->factory->make($defaultProvider);
        }

        return $this->provider;
    }

    /**
     * Get list of available providers.
     */
    public function availableProviders(): array
    {
        return $this->factory->getAvailableProviders();
    }
}
