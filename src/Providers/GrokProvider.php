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
            // Use direct HTTP request to bypass the buggy client library
            $response = $this->makeDirectRequest($messagesArray, $optionsArray);

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
                // Try to get more specific error information
                $errorDetails = $this->getDetailedErrorInfo($e);
                throw new AIException(
                    "Grok AI Error: {$errorDetails}",
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
     * Validate API key format and provide detailed feedback.
     */
    public function validateApiKey(): array
    {
        $apiKey = $this->config['api_key'] ?? '';
        
        if (empty($apiKey)) {
            return [
                'valid' => false,
                'error' => 'No API key provided',
                'suggestion' => 'Please set GROK_API_KEY in your environment or config'
            ];
        }
        
        if (!str_starts_with($apiKey, 'xai-')) {
            return [
                'valid' => false,
                'error' => 'Invalid API key format',
                'suggestion' => 'Grok API keys should start with "xai-". Get your key from https://console.x.ai'
            ];
        }
        
        if (strlen($apiKey) < 20) {
            return [
                'valid' => false,
                'error' => 'API key too short',
                'suggestion' => 'Please check your Grok API key - it should be longer'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'API key format looks correct',
            'prefix' => substr($apiKey, 0, 10) . '...'
        ];
    }

    /**
     * Make direct HTTP request to Grok API (bypassing buggy client library).
     */
    protected function makeDirectRequest(array $messages, array $options): array
    {
        $url = 'https://api.x.ai/v1/chat/completions';
        
        $data = [
            'messages' => $messages,
            'model' => $options['model'] ?? $this->model,
            'stream' => $options['stream'] ?? false,
            'temperature' => $options['temperature'] ?? $this->temperature,
        ];
        
        // Add max_tokens if specified
        if (isset($options['max_tokens']) || isset($this->maxTokens)) {
            $data['max_tokens'] = $options['max_tokens'] ?? $this->maxTokens;
        }
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config['api_key']
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new AIException("cURL Error: $error", 0);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "HTTP $httpCode error";
            throw new AIException("Grok API Error: $errorMessage", $httpCode);
        }
        
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AIException("Invalid JSON response from Grok API", 0);
        }
        
        return $responseData;
    }

    /**
     * Get detailed error information for better debugging.
     */
    protected function getDetailedErrorInfo(\TypeError $e): string
    {
        $apiKey = $this->config['api_key'] ?? 'not-set';
        $apiKeyPrefix = substr($apiKey, 0, 10);
        
        // Check if API key format is correct
        if (empty($apiKey) || $apiKey === 'not-set') {
            return "No API key provided. Please set GROK_API_KEY in your environment.";
        }
        
        if (!str_starts_with($apiKey, 'xai-')) {
            return "Invalid API key format. Grok API keys should start with 'xai-'. Got: {$apiKeyPrefix}...";
        }
        
        if (strlen($apiKey) < 20) {
            return "API key appears to be too short. Please check your Grok API key.";
        }
        
        return "API key format looks correct ({$apiKeyPrefix}...), but request failed. This could be due to: 1) Invalid API key, 2) Insufficient credits, 3) API endpoint issues, or 4) Rate limiting.";
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
            // Handle newer models that might not be in the enum yet
            'grok-4' => Model::GROK_2_LATEST, // Fallback to latest available
            'grok-4-0709' => Model::GROK_2_LATEST, // Fallback to latest available
            default => Model::GROK_2,
        };
    }
}
