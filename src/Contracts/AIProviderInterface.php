<?php

namespace Homa\Contracts;

use Homa\Response\AIResponse;

interface AIProviderInterface
{
    /**
     * Send a message to the AI and get a response.
     *
     * @param array $messages
     * @param array $options
     * @return AIResponse
     */
    public function sendMessage(array $messages, array $options = []): AIResponse;

    /**
     * Set the model to use for this provider.
     *
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self;

    /**
     * Set the temperature for responses.
     *
     * @param float $temperature
     * @return self
     */
    public function setTemperature(float $temperature): self;

    /**
     * Set the maximum tokens for responses.
     *
     * @param int $maxTokens
     * @return self
     */
    public function setMaxTokens(int $maxTokens): self;

    /**
     * Validate the configuration for this provider.
     *
     * @return bool
     */
    public function validateConfig(): bool;
}
