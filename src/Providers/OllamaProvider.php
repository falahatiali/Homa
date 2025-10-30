<?php

namespace Homa\Providers;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Homa\Contracts\AIProviderInterface;
use Homa\Exceptions\AIException;
use Homa\Response\AIResponse;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;

/**
 * Ollama local provider (runs against a local Ollama server).
 *
 * Defaults:
 * - Base URL: http://localhost:11434
 * - Chat endpoint: /api/chat
 */
class OllamaProvider implements AIProviderInterface
{
    protected array $config;
    protected string $model;
    protected float $temperature;
    protected int $maxTokens;
    protected HttpClient $http;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->model = $config['model'] ?? 'llama3';
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? 1024;

        $timeout = $config['timeout'] ?? 300;
        // Use 0.0 for unlimited; or omit the key entirely if you prefer default behavior
        $timeoutOption = ($timeout === 0 || $timeout === '0') ? 0.0 : (float) $timeout;

        $clientOptions = [
            'base_uri' => $config['api_url'] ?? 'http://localhost:11434',
            'connect_timeout' => isset($config['connect_timeout']) ? (float) $config['connect_timeout'] : 10.0,
            'curl' => [
                CURLOPT_TCP_NODELAY => true,
                CURLOPT_BUFFERSIZE => 16384,
            ],
        ];

        if ($timeoutOption !== null) {
            $clientOptions['timeout'] = $timeoutOption;
        }
        
        $this->http = new HttpClient($clientOptions);
    }

    public function sendMessage(MessageCollection|array $messages, RequestOptions|array|null $options = null): AIResponse
    {
        $messagesArray = $messages instanceof MessageCollection ? $messages->toArray() : $messages;
        $optionsArray = match (true) {
            $options instanceof RequestOptions => $options->toArray(),
            is_array($options) => $options,
            default => [],
        };

        $payload = [
            'model' => $optionsArray['model'] ?? $this->model,
            'messages' => $this->normalizeToOllamaMessages($messagesArray),
            'options' => [
                'temperature' => $optionsArray['temperature'] ?? $this->temperature,
                'num_predict' => $optionsArray['max_tokens'] ?? $this->maxTokens,
                'num_ctx' => $this->config['num_ctx'],
                'num_thread' => $this->config['num_thread'],
            ],
            'stream' => false,
        ];

        try {
            $response = $this->http->post('/api/chat', [
                'json' => $payload,
            ]);

            $data = json_decode((string) $response->getBody(), true);
            if (!is_array($data)) {
                $raw = (string) $response->getBody();
                throw new AIException('Invalid response from Ollama: ' . $raw);
            }

            $content = $data['message']['content'] ?? ($data['response'] ?? '');
            $model = $data['model'] ?? ($optionsArray['model'] ?? $this->model);

            return new AIResponse(
                content: $content,
                model: $model,
                usage: [
                    'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
                    'completion_tokens' => $data['eval_count'] ?? 0,
                    'total_tokens' => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
                ],
                raw: $data
            );
        } catch (GuzzleException $e) {
            throw new AIException(
                'Ollama HTTP Error: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (\Throwable $e) {
            throw new AIException(
                'Error processing Ollama response: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    protected function normalizeToOllamaMessages(array $messages): array
    {
        $out = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user';
            $content = $m['content'] ?? '';

            // Ollama expects roles: system|user|assistant
            if (! in_array($role, ['system', 'user', 'assistant'], true)) {
                $role = 'user';
            }
            $out[] = [
                'role' => $role,
                'content' => $content,
            ];
        }
        return $out;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function validateConfig(): bool
    {
        // No API key required for local Ollama
        return true;
    }

    public function getAvailableModels(): array
    {
        // Informative defaults
        return [
            'llama3',
            'llama3.1:8b-instruct',
            'mistral:7b-instruct',
            'qwen2.5:7b-instruct',
            'phi3:mini',
            'gemma:7b-instruct',
        ];
    }
}



