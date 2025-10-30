<?php

namespace Homa\Providers;

use Gemini;
use Gemini\Client;
use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\AIException;
use Homa\Response\AIResponse;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;

/**
 * Google Gemini Provider implementation.
 *
 * Uses Google's Gemini API for advanced AI capabilities including
 * vision, video, and multimodal understanding.
 */
class GeminiProvider implements AIProviderInterface
{
    /**
     * The Gemini client instance.
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
     * Create a new Gemini Provider instance.
     *
     * @param  array  $config  Configuration array
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->model = $config['model'] ?? 'gemini-2.0-flash-exp';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 1000;

        // Initialize Gemini client
        $this->client = Gemini::factory()
            ->withApiKey($config['api_key'] ?? '')
            ->withBaseUrl($config['base_uri'] ?? 'https://generativelanguage.googleapis.com/v1beta')
            ->make();
    }

    /**
     * Send a message to Gemini.
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
            // Extract system prompt if present
            $systemPrompt = $this->extractSystemPrompt($messagesArray);
            
            // Build generation configuration
            $generationConfig = new \Gemini\Data\GenerationConfig(
                temperature: $optionsArray['temperature'] ?? $this->temperature,
                maxOutputTokens: $optionsArray['max_tokens'] ?? $this->maxTokens,
            );
            
            // Create the model
            $model = $this->client->generativeModel(
                model: $optionsArray['model'] ?? $this->model
            );
            
            // Apply generation config
            $model = $model->withGenerationConfig($generationConfig);
            
            // Add system instruction if present (must be a Content object)
            if ($systemPrompt) {
                $model = $model->withSystemInstruction(
                    \Gemini\Data\Content::parse($systemPrompt)
                );
            }

            // Convert messages to Gemini format (just user messages)
            $contents = $this->formatMessagesForGemini($messagesArray);
            
            // Ensure we have content to send
            if (empty(trim($contents))) {
                throw new AIException('No content to send to Gemini');
            }

            // Generate content (pass as variadic arguments)
            $response = $model->generateContent($contents);

            // Extract response safely
            $content = '';
            $usage = [];
            
            try {
                $content = $response->text() ?? '';
            } catch (\Exception $e) {
                // If text() fails, try to get raw content
                $content = '';
            }
            
            try {
                $usage = [
                    'prompt_tokens' => $response->usageMetadata->promptTokenCount ?? 0,
                    'completion_tokens' => $response->usageMetadata->candidatesTokenCount ?? 0,
                    'total_tokens' => $response->usageMetadata->totalTokenCount ?? 0,
                ];
            } catch (\Exception $e) {
                $usage = [];
            }
            
            return new AIResponse(
                content: $content,
                model: $optionsArray['model'] ?? $this->model,
                usage: $usage,
                raw: []
            );
        } catch (\Gemini\Exceptions\ErrorException $e) {
            throw new AIException(
                "Gemini API Error: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (\Gemini\Exceptions\UnserializableResponse $e) {
            // This usually means invalid API key or invalid response from API
            throw new AIException(
                "Gemini API Response Error: {$e->getMessage()}. Please check your API key and request format.",
                $e->getCode(),
                $e
            );
        } catch (\Exception $e) {
            throw new AIException(
                "Error processing Gemini response: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Extract system prompt from messages.
     *
     * @param  array  $messages
     * @return string|null
     */
    protected function extractSystemPrompt(array $messages): ?string
    {
        foreach ($messages as $message) {
            if (($message['role'] ?? 'user') === 'system') {
                return $message['content'] ?? null;
            }
        }

        return null;
    }

    /**
     * Format messages for Gemini API.
     *
     * @param  array  $messages
     * @return string
     */
    protected function formatMessagesForGemini(array $messages): string
    {
        // Gemini accepts text input directly
        // Extract only user messages (system is handled separately)
        $text = '';

        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';

            // Only include user messages, skip system and assistant
            if ($role === 'user') {
                $text .= $content . "\n\n";
            }
        }

        return trim($text);
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
        return !empty($this->config['api_key']);
    }

    /**
     * Get available Gemini models.
     *
     * @return array
     */
    public function getAvailableModels(): array
    {
        return [
            'gemini-2.0-flash-exp',
            'gemini-1.5-pro-latest',
            'gemini-1.5-flash-latest',
            'gemini-1.5-pro',
            'gemini-1.5-flash',
            'gemini-1.5-pro-002',
            'gemini-1.5-flash-002',
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
