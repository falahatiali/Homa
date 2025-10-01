<?php

namespace Homa\Providers;

use GrokPHP\Client\Client;
use GrokPHP\Client\Config\ChatOptions;
use GrokPHP\Client\Enums\Model;
use GrokPHP\Client\Exceptions\GrokException;
use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\AIException;
use Homa\Response\AIResponse;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;

/**
 * Grok AI Provider implementation.
 *
 * Uses Adapter Pattern to wrap Grok PHP client.
 * Implements Strategy Pattern for AI provider abstraction.
 */
class GrokProvider implements AIProviderInterface
{
    /**
     * The Grok client instance.
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
     * Create a new Grok Provider instance.
     *
     * @param  array  $config  Configuration array
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->model = $config['model'] ?? 'grok-2';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 1000;

        // Initialize Grok client
        $this->client = new Client($config['api_key']);
    }

    /**
     * Send a message to Grok AI.
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
            // Prepare chat options
            $chatOptions = new ChatOptions(
                model: $this->getGrokModel($optionsArray['model'] ?? $this->model),
                temperature: $optionsArray['temperature'] ?? $this->temperature,
                maxTokens: $optionsArray['max_tokens'] ?? $this->maxTokens,
                stream: $optionsArray['stream'] ?? false
            );

            // Send request to Grok
            $response = $this->client->chat($messagesArray, $chatOptions);

            return new AIResponse(
                content: $response->content(),
                model: $response->model(),
                usage: [
                    'prompt_tokens' => $response->usage()['prompt_tokens'] ?? 0,
                    'completion_tokens' => $response->usage()['completion_tokens'] ?? 0,
                    'total_tokens' => $response->usage()['total_tokens'] ?? 0,
                ],
                raw: $response->toArray()
            );
        } catch (GrokException $e) {
            // Wrap Grok exceptions into our exception type
            throw new AIException(
                "Grok AI Error: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (\Exception $e) {
            throw new AIException(
                "Error processing Grok response: {$e->getMessage()}",
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
     * Convert string model name to Grok Model enum.
     */
    protected function getGrokModel(string $model): Model
    {
        return match ($model) {
            'grok-2' => Model::GROK_2,
            'grok-2-latest' => Model::GROK_2_LATEST,
            'grok-2-1212' => Model::GROK_2_1212,
            'grok-2-vision' => Model::GROK_2_VISION,
            'grok-2-vision-latest' => Model::GROK_2_VISION_LATEST,
            'grok-2-vision-1212' => Model::GROK_2_VISION_1212,
            'grok-vision-beta' => Model::GROK_VISION_BETA,
            'grok-beta' => Model::GROK_BETA,
            default => Model::GROK_2,
        };
    }
}
