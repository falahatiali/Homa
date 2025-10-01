<?php

namespace Homa\Response;

class AIResponse
{
    /**
     * The response content.
     */
    protected string $content;

    /**
     * The model used for the response.
     */
    protected ?string $model;

    /**
     * Usage statistics.
     */
    protected array $usage;

    /**
     * Raw response data.
     */
    protected array $raw;

    /**
     * Create a new AI Response instance.
     */
    public function __construct(string $content, ?string $model = null, array $usage = [], array $raw = [])
    {
        $this->content = $content;
        $this->model = $model;
        $this->usage = $usage;
        $this->raw = $raw;
    }

    /**
     * Get the response content.
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Get the model used.
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * Get usage statistics.
     */
    public function usage(): array
    {
        return $this->usage;
    }

    /**
     * Get raw response data.
     */
    public function raw(): array
    {
        return $this->raw;
    }

    /**
     * Get the response as a string.
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Convert the response to an array.
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'model' => $this->model,
            'usage' => $this->usage,
        ];
    }

    /**
     * Convert the response to JSON.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
