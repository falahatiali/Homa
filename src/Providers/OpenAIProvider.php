<?php

namespace Homa\Providers;

use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\AIException;
use Homa\Response\AIResponse;
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
     * @throws AIException
     */
    public function sendMessage(array $messages, array $options = []): AIResponse
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? $this->temperature,
                'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
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
}
