<?php

namespace Homa\Providers;

use GrokPHP\Client\Clients\GrokClient;
use GrokPHP\Client\Config\ChatOptions;
use GrokPHP\Client\Config\GrokConfig;
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
    protected GrokClient $client;

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
     * @param array $config Configuration array
     * @throws GrokException
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->model = $config['model'] ?? 'grok-2';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 1000;

        // Initialize Grok client with proper config
        $grokConfig = new GrokConfig(
            apiKey: $config['api_key'],
            baseUri: $config['base_uri'] ?? 'https://api.x.ai/v1',
            timeout: $config['timeout'] ?? 30
        );

        $this->client = new GrokClient($grokConfig);
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
                stream: $optionsArray['stream'] ?? false
            );

            // Send request to Grok
            $response = $this->client->chat($messagesArray, $chatOptions);

            // Parse the response correctly
            $content = '';
            $model = $this->model;
            $usage = ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0];

            if (isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
            }

            if (isset($response['model'])) {
                $model = $response['model'];
            }

            if (isset($response['usage'])) {
                $usage = [
                    'prompt_tokens' => $response['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $response['usage']['completion_tokens'] ?? 0,
                    'total_tokens' => $response['usage']['total_tokens'] ?? 0,
                ];
            }

            return new AIResponse(
                content: $content,
                model: $model,
                usage: $usage,
                raw: $response
            );
        } catch (\TypeError $e) {
            // Handle the Grok client library bug where it passes array instead of string
            if (str_contains($e->getMessage(), 'GrokException::__construct()')) {
                throw new AIException(
                    "Grok AI Error: Invalid API key or request format",
                    400,
                    $e
                );
            }
            throw new AIException(
                "Grok AI Error: {$e->getMessage()}",
                0,
                $e
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
