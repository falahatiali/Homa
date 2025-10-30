<?php

namespace Homa\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\AIException;
use Homa\Response\AIResponse;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;

class AnthropicProvider implements AIProviderInterface
{
    /**
     * The HTTP client instance.
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
     * Create a new Anthropic Provider instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->model = $config['model'] ?? 'claude-3-5-sonnet-20241022';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 1000;

        $this->client = new Client([
            'base_uri' => $config['api_url'] ?? 'https://api.anthropic.com/v1/',
            'timeout' => $config['timeout'] ?? 30,
            'headers' => [
                'x-api-key' => $config['api_key'] ?? '',
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a message to Anthropic.
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
            // Anthropic API requires system message to be separate
            $systemMessage = null;
            $conversationMessages = [];

            foreach ($messagesArray as $message) {
                if ($message['role'] === 'system') {
                    $systemMessage = $message['content'];
                } else {
                    $conversationMessages[] = $message;
                }
            }

            $payload = [
                'model' => $optionsArray['model'] ?? $this->model,
                'messages' => $conversationMessages,
                'temperature' => $optionsArray['temperature'] ?? $this->temperature,
                'max_tokens' => $optionsArray['max_tokens'] ?? $this->maxTokens,
            ];

            if ($systemMessage) {
                $payload['system'] = $systemMessage;
            }

            $response = $this->client->post('messages', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return new AIResponse(
                content: $data['content'][0]['text'] ?? '',
                model: $data['model'] ?? null,
                usage: $data['usage'] ?? [],
                raw: $data
            );
        } catch (GuzzleException $e) {
            throw new AIException('Anthropic API Error: '.$e->getMessage(), $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new AIException('Error processing Anthropic response: '.$e->getMessage(), 0, $e);
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
        return !empty($this->config['api_key']);
    }
}
