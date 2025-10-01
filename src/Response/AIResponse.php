<?php

namespace LaravelSage\Response;

class AIResponse
{
    /**
     * The response content.
     *
     * @var string
     */
    protected string $content;

    /**
     * The model used for the response.
     *
     * @var string|null
     */
    protected ?string $model;

    /**
     * Usage statistics.
     *
     * @var array
     */
    protected array $usage;

    /**
     * Raw response data.
     *
     * @var array
     */
    protected array $raw;

    /**
     * Create a new AI Response instance.
     *
     * @param string $content
     * @param string|null $model
     * @param array $usage
     * @param array $raw
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
     *
     * @return string
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Get the model used.
     *
     * @return string|null
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * Get usage statistics.
     *
     * @return array
     */
    public function usage(): array
    {
        return $this->usage;
    }

    /**
     * Get raw response data.
     *
     * @return array
     */
    public function raw(): array
    {
        return $this->raw;
    }

    /**
     * Get the response as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Convert the response to an array.
     *
     * @return array
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
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
