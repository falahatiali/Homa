<?php

namespace Homa\ValueObjects;

use JsonSerializable;

/**
 * Represents a single message in a conversation.
 *
 * Immutable value object ensuring type safety and consistency
 * across all AI providers.
 */
class Message implements JsonSerializable
{
    /**
     * Create a new message instance.
     *
     * @param  string  $role  The role of the message sender (user, assistant, system)
     * @param  string  $content  The content of the message
     * @param  string|null  $name  Optional name of the message sender
     */
    public function __construct(
        public readonly string $role,
        public readonly string $content,
        public readonly ?string $name = null
    ) {
        $this->validateRole($role);
    }

    /**
     * Create a user message.
     */
    public static function user(string $content, ?string $name = null): self
    {
        return new self('user', $content, $name);
    }

    /**
     * Create an assistant message.
     */
    public static function assistant(string $content, ?string $name = null): self
    {
        return new self('assistant', $content, $name);
    }

    /**
     * Create a system message.
     */
    public static function system(string $content): self
    {
        return new self('system', $content);
    }

    /**
     * Convert the message to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'role' => $this->role,
            'content' => $this->content,
            'name' => $this->name,
        ], fn ($value) => $value !== null);
    }

    /**
     * Convert the message to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Validate the message role.
     *
     * @throws \InvalidArgumentException
     */
    private function validateRole(string $role): void
    {
        $validRoles = ['user', 'assistant', 'system'];

        if (! in_array($role, $validRoles)) {
            throw new \InvalidArgumentException(
                "Invalid message role '{$role}'. Must be one of: ".implode(', ', $validRoles)
            );
        }
    }
}
