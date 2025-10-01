<?php

namespace Homa\Contracts;

use Homa\Response\AIResponse;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;

interface AIProviderInterface
{
    /**
     * Send a message to the AI and get a response.
     *
     * @param  MessageCollection|array  $messages  The messages to send (supports backward compatibility)
     * @param  RequestOptions|array|null  $options  Request options (supports backward compatibility)
     */
    public function sendMessage(MessageCollection|array $messages, RequestOptions|array|null $options = null): AIResponse;

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
