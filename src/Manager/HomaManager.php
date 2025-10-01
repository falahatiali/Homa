<?php

namespace Homa\Manager;

use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Homa\Contracts\AIProviderInterface;
use Homa\Providers\AnthropicProvider;
use Homa\Providers\OpenAIProvider;
use Homa\Response\AIResponse;
use Homa\Conversation\Conversation;

class HomaManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The active provider instance.
     *
     * @var AIProviderInterface|null
     */
    protected ?AIProviderInterface $provider = null;

    /**
     * Custom configuration for this instance.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Create a new Homa Manager instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Set the AI provider.
     *
     * @param string $provider
     * @return $this
     */
    public function provider(string $provider): self
    {
        $this->provider = $this->createProvider($provider);
        return $this;
    }

    /**
     * Set the model to use.
     *
     * @param string $model
     * @return $this
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
     * @param float $temperature
     * @return $this
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
     * @param int $maxTokens
     * @return $this
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
     * @param string $prompt
     * @return $this
     */
    public function systemPrompt(string $prompt): self
    {
        $this->config['system_prompt'] = $prompt;
        return $this;
    }

    /**
     * Ask a simple question.
     *
     * @param string $question
     * @return AIResponse
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
     *
     * @param string|array $messages
     * @return AIResponse
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
     *
     * @return Conversation
     */
    public function startConversation(): Conversation
    {
        return new Conversation($this->getProvider(), $this->config);
    }

    /**
     * Get the active provider instance.
     *
     * @return AIProviderInterface
     */
    protected function getProvider(): AIProviderInterface
    {
        if (!$this->provider) {
            $defaultProvider = config('homa.default', 'openai');
            $this->provider = $this->createProvider($defaultProvider);
        }

        return $this->provider;
    }

    /**
     * Create a provider instance.
     *
     * @param string $provider
     * @return AIProviderInterface
     * @throws InvalidArgumentException
     */
    protected function createProvider(string $provider): AIProviderInterface
    {
        $config = config("homa.providers.{$provider}");

        if (!$config) {
            throw new InvalidArgumentException("Provider [{$provider}] is not configured.");
        }

        return match ($provider) {
            'openai' => new OpenAIProvider($config),
            'anthropic' => new AnthropicProvider($config),
            default => throw new InvalidArgumentException("Provider [{$provider}] is not supported."),
        };
    }
}

