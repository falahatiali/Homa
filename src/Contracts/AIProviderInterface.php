<?php

namespace Homa\Contracts;

use Homa\Response\AIResponse;

interface AIProviderInterface
{
    /**
     * Send a message to the AI and get a response.
     */
    public function sendMessage(array $messages, array $options = []): AIResponse;

    /**
     * Set the model to use for this provider.
     */
    public function setModel(string $model): self;

    /**
     * Set the temperature for responses.
     */
    public function setTemperature(float $temperature): self;

    /**
     * Set the maximum tokens for responses.
     */
    public function setMaxTokens(int $maxTokens): self;

    /**
     * Validate the configuration for this provider.
     */
    public function validateConfig(): bool;
}
