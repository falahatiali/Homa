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
 * Groq Provider implementation.
 *
 * Uses Groq's OpenAI-compatible API for ultra-fast LLM inference.
 * Extends OpenAI provider functionality with Groq-specific optimizations.
 */
class GroqProvider implements AIProviderInterface
{
    /**
     * The OpenAI client instance configured for Groq.
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
     * Create a new Groq Provider instance.
     *
     * @param  array  $config  Configuration array
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->model = $config['model'] ?? 'openai/gpt-oss-20b';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 1000;

        // Use OpenAI client with Groq's base URL
        $this->client = OpenAI::factory()
            ->withApiKey($config['api_key'])
            ->withBaseUri($config['api_url'] ?? 'https://api.groq.com/openai/v1')
            ->withHttpHeader('User-Agent', 'Homa-Laravel-AI/1.0')
            ->make();
    }

    /**
     * Send a message to Groq.
     *
     * Supports both Value Objects and arrays for backward compatibility.
     *
     * @param  MessageCollection|array  $messages
     * @param  RequestOptions|array|null  $options
     * @return AIResponse
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
            $response = $this->client->chat()->create([
                'model' => $optionsArray['model'] ?? $this->model,
                'messages' => $messagesArray,
                'temperature' => $optionsArray['temperature'] ?? $this->temperature,
                'max_tokens' => $optionsArray['max_tokens'] ?? $this->maxTokens,
                'stream' => $optionsArray['stream'] ?? false,
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
                "Groq API Error: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (\Exception $e) {
            throw new AIException(
                "Error processing Groq response: {$e->getMessage()}",
                0,
                $e
            );
        }
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
     * Validate the configuration.
     */
    public function validateConfig(): bool
    {
        return ! empty($this->config['api_key']);
    }

    /**
     * Get available Groq models.
     *
     * @return array
     */
    public function getAvailableModels(): array
    {
        return [
            'openai/gpt-oss-20b',
            'openai/gpt-oss-7b',
            'llama-3.1-70b-versatile',
            'llama-3.1-8b-instant',
            'mixtral-8x7b-32768',
            'gemma-7b-it',
        ];
    }

    /**
     * Get the current model.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get the current temperature.
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    /**
     * Get the current max tokens.
     */
    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }
}
