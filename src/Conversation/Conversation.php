<?php

namespace Homa\Conversation;

use Homa\Contracts\AIProviderInterface;
use Homa\Response\AIResponse;

class Conversation
{
    /**
     * The AI provider instance.
     */
    protected AIProviderInterface $provider;

    /**
     * The conversation messages.
     */
    protected array $messages = [];

    /**
     * Configuration options.
     */
    protected array $config;

    /**
     * Create a new Conversation instance.
     */
    public function __construct(AIProviderInterface $provider, array $config = [])
    {
        $this->provider = $provider;
        $this->config = $config;

        // Add system prompt if configured
        if (isset($config['system_prompt'])) {
            $this->messages[] = [
                'role' => 'system',
                'content' => $config['system_prompt'],
            ];
        } elseif ($systemPrompt = config('homa.system_prompt')) {
            $this->messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }
    }

    /**
     * Add a user message and get a response.
     */
    public function ask(string $message): AIResponse
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $response = $this->provider->sendMessage($this->messages, $this->config);

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response->content(),
        ];

        return $response;
    }

    /**
     * Add a system message to the conversation.
     *
     * @return $this
     */
    public function system(string $message): self
    {
        $this->messages[] = [
            'role' => 'system',
            'content' => $message,
        ];

        return $this;
    }

    /**
     * Get all messages in the conversation.
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Clear the conversation history.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->messages = [];

        return $this;
    }

    /**
     * Get the conversation history as a string.
     */
    public function history(): string
    {
        return collect($this->messages)
            ->map(fn ($msg) => "{$msg['role']}: {$msg['content']}")
            ->implode("\n\n");
    }
}
