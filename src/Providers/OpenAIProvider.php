<?php

namespace Homa\Providers;

use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\AIException;
use Homa\Response\AIResponse;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;
use OpenAI;
use OpenAI\Client;

/**
 * OpenAI Provider implementation.
 *
 * Uses Adapter Pattern to wrap OpenAI PHP client.
 * Implements Strategy Pattern for AI provider abstraction.
 */
class OpenAIProvider implements AIProviderInterface
{
    /**
     * The OpenAI client instance.
     */
    protected Client $client;

    /**
     * Provider configuration.
     */
    protected array $config;

    /**
     * The model to use.
     */
    protected string $model;

    /**
     * The temperature setting.
     */
    protected float $temperature;

    /**
     * The max tokens setting.
     */
    protected int $maxTokens;

    /**
     * Create a new OpenAI Provider instance.
     *
     * @param  array  $config  Configuration array
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->model = $config['model'] ?? 'gpt-4';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 1000;

        // Use the official OpenAI PHP client
        $this->client = OpenAI::factory()
            ->withApiKey($config['api_key'])
            ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
            ->withBaseUri($config['api_url'] ?? 'https://api.openai.com/v1')
            ->make();
    }

    /**
     * Send a message to OpenAI.
     *
     * Supports both Value Objects and arrays for backward compatibility.
     *
     *
     * @throws AIException
     */
    public function sendMessage(MessageCollection|array $messages, RequestOptions|array|null $options = null): AIResponse
    {
        // Normalize messages to array
        $messagesArray = $messages instanceof MessageCollection
            ? $messages->toArray()
            : $messages;

        // Normalize options to array
        $optionsArray = match (true) {
            $options instanceof RequestOptions => $options->toArray(),
            is_array($options) => $options,
            default => [],
        };

        try {
            $model = $optionsArray['model'] ?? $this->model;
            $maxTokens = $optionsArray['max_tokens'] ?? $this->maxTokens;
            
            // GPT-5 models use max_completion_tokens instead of max_tokens
            $tokenParam = $this->isGpt5Model($model) ? 'max_completion_tokens' : 'max_tokens';
            
            $response = $this->client->chat()->create([
                'model' => $model,
                'messages' => $messagesArray,
                'temperature' => $optionsArray['temperature'] ?? $this->temperature,
                $tokenParam => $maxTokens,
            ]);

            return new AIResponse(
                content: $response->choices[0]->message->content ?? '',
                model: $response->model,
                usage: [
                    'prompt_tokens' => $response->usage->promptTokens,
                    'completion_tokens' => $response->usage->completionTokens,
                    'total_tokens' => $response->usage->totalTokens,
                ],
                raw: $response->toArray()
            );
        } catch (\OpenAI\Exceptions\ErrorException $e) {
            // Wrap OpenAI exceptions into our exception type
            throw new AIException(
                "OpenAI API Error: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (\Exception $e) {
            throw new AIException(
                "Error processing OpenAI response: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Check if the model is a GPT-5 model that requires max_completion_tokens.
     *
     * @param  string  $model
     * @return bool
     */
    protected function isGpt5Model(string $model): bool
    {
        return str_starts_with($model, 'gpt-5') || str_starts_with($model, 'gpt-5o');
    }

    /**
     * Set the model.
     *
     * @return $this
     */
    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the temperature.
     *
     * @return $this
     */
    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Set the max tokens.
     *
     * @return $this
     */
    public function setMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    /**
     * Get available OpenAI models.
     *
     * @return array
     */
    public function getAvailableModels(): array
    {
        return [
            'gpt-5',
            'gpt-5o',
            'gpt-4o',
            'gpt-4o-mini',
            'gpt-4-turbo',
            'gpt-4',
            'gpt-3.5-turbo',
        ];
    }

    /**
     * Validate the configuration.
     */
    public function validateConfig(): bool
    {
        return !empty($this->config['api_key']);
    }
}
