<?php

namespace Homa\ValueObjects;

use JsonSerializable;

/**
 * Represents configuration options for AI requests.
 *
 * Immutable value object providing type-safe configuration
 * with sensible defaults.
 */
class RequestOptions implements JsonSerializable
{
    /**
     * Create a new request options instance.
     *
     * @param  string|null  $model  The AI model to use
     * @param  float|null  $temperature  Randomness of responses (0.0 to 2.0)
     * @param  int|null  $maxTokens  Maximum tokens in the response
     * @param  bool|null  $stream  Whether to stream the response
     * @param  float|null  $topP  Nucleus sampling parameter
     * @param  int|null  $n  Number of completions to generate
     * @param  array|null  $stop  Stop sequences
     * @param  float|null  $presencePenalty  Penalize new tokens based on presence
     * @param  float|null  $frequencyPenalty  Penalize new tokens based on frequency
     */
    public function __construct(
        public readonly ?string $model = null,
        public readonly ?float $temperature = null,
        public readonly ?int $maxTokens = null,
        public readonly ?bool $stream = null,
        public readonly ?float $topP = null,
        public readonly ?int $n = null,
        public readonly ?array $stop = null,
        public readonly ?float $presencePenalty = null,
        public readonly ?float $frequencyPenalty = null
    ) {
        $this->validate();
    }

    /**
     * Create from an array.
     */
    public static function fromArray(array $options): self
    {
        return new self(
            model: $options['model'] ?? null,
            temperature: $options['temperature'] ?? null,
            maxTokens: $options['max_tokens'] ?? null,
            stream: $options['stream'] ?? null,
            topP: $options['top_p'] ?? null,
            n: $options['n'] ?? null,
            stop: $options['stop'] ?? null,
            presencePenalty: $options['presence_penalty'] ?? null,
            frequencyPenalty: $options['frequency_penalty'] ?? null
        );
    }

    /**
     * Create options with only model specified.
     */
    public static function withModel(string $model): self
    {
        return new self(model: $model);
    }

    /**
     * Create options for creative responses.
     */
    public static function creative(): self
    {
        return new self(temperature: 1.2);
    }

    /**
     * Create options for factual/deterministic responses.
     */
    public static function deterministic(): self
    {
        return new self(temperature: 0.0);
    }

    /**
     * Create options for balanced responses.
     */
    public static function balanced(): self
    {
        return new self(temperature: 0.7);
    }

    /**
     * Merge with another options instance.
     */
    public function merge(RequestOptions $other): self
    {
        return new self(
            model: $other->model ?? $this->model,
            temperature: $other->temperature ?? $this->temperature,
            maxTokens: $other->maxTokens ?? $this->maxTokens,
            stream: $other->stream ?? $this->stream,
            topP: $other->topP ?? $this->topP,
            n: $other->n ?? $this->n,
            stop: $other->stop ?? $this->stop,
            presencePenalty: $other->presencePenalty ?? $this->presencePenalty,
            frequencyPenalty: $other->frequencyPenalty ?? $this->frequencyPenalty
        );
    }

    /**
     * Convert to array, filtering out null values.
     */
    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'stream' => $this->stream,
            'top_p' => $this->topP,
            'n' => $this->n,
            'stop' => $this->stop,
            'presence_penalty' => $this->presencePenalty,
            'frequency_penalty' => $this->frequencyPenalty,
        ], fn ($value) => $value !== null);
    }

    /**
     * Convert the options to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Validate the option values.
     *
     * @throws \InvalidArgumentException
     */
    private function validate(): void
    {
        if ($this->temperature !== null && ($this->temperature < 0 || $this->temperature > 2)) {
            throw new \InvalidArgumentException('Temperature must be between 0 and 2');
        }

        if ($this->maxTokens !== null && $this->maxTokens < 1) {
            throw new \InvalidArgumentException('Max tokens must be at least 1');
        }

        if ($this->topP !== null && ($this->topP < 0 || $this->topP > 1)) {
            throw new \InvalidArgumentException('Top P must be between 0 and 1');
        }

        if ($this->n !== null && $this->n < 1) {
            throw new \InvalidArgumentException('N must be at least 1');
        }

        if ($this->presencePenalty !== null && ($this->presencePenalty < -2 || $this->presencePenalty > 2)) {
            throw new \InvalidArgumentException('Presence penalty must be between -2 and 2');
        }

        if ($this->frequencyPenalty !== null && ($this->frequencyPenalty < -2 || $this->frequencyPenalty > 2)) {
            throw new \InvalidArgumentException('Frequency penalty must be between -2 and 2');
        }
    }
}
