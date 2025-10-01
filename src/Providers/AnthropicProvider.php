<?php

namespace LaravelSage\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LaravelSage\Contracts\AIProviderInterface;
use LaravelSage\Exceptions\AIException;
use LaravelSage\Response\AIResponse;

class AnthropicProvider implements AIProviderInterface
{
    /**
     * The HTTP client instance.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Provider configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * The model to use.
     *
     * @var string
     */
    protected string $model;

    /**
     * The temperature setting.
     *
     * @var float
     */
    protected float $temperature;

    /**
     * The max tokens setting.
     *
     * @var int
     */
    protected int $maxTokens;

    /**
     * Create a new Anthropic Provider instance.
     *
     * @param array $config
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
                'x-api-key' => $config['api_key'],
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a message to Anthropic.
     *
     * @param array $messages
     * @param array $options
     * @return AIResponse
     * @throws AIException
     */
    public function sendMessage(array $messages, array $options = []): AIResponse
    {
        try {
            // Anthropic API requires system message to be separate
            $systemMessage = null;
            $conversationMessages = [];

            foreach ($messages as $message) {
                if ($message['role'] === 'system') {
                    $systemMessage = $message['content'];
                } else {
                    $conversationMessages[] = $message;
                }
            }

            $payload = [
                'model' => $options['model'] ?? $this->model,
                'messages' => $conversationMessages,
                'temperature' => $options['temperature'] ?? $this->temperature,
                'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
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
            throw new AIException("Anthropic API Error: " . $e->getMessage(), $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new AIException("Error processing Anthropic response: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Set the model.
     *
     * @param string $model
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
     * @param float $temperature
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
     * @param int $maxTokens
     * @return $this
     */
    public function setMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    /**
     * Validate the configuration.
     *
     * @return bool
     */
    public function validateConfig(): bool
    {
        return !empty($this->config['api_key']);
    }
}
