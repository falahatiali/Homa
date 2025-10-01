<?php

namespace Homa\ValueObjects;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Represents a collection of messages.
 *
 * Provides a fluent interface for building message arrays
 * with type safety and validation.
 *
 * @implements ArrayAccess<int, Message>
 * @implements IteratorAggregate<int, Message>
 */
class MessageCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @var array<Message>
     */
    private array $messages = [];

    /**
     * Create a new message collection.
     *
     * @param  array<Message|array>  $messages
     */
    public function __construct(array $messages = [])
    {
        foreach ($messages as $message) {
            $this->add($message);
        }
    }

    /**
     * Create a collection from an array of messages.
     *
     * @param  array<array>  $messages
     */
    public static function fromArray(array $messages): self
    {
        return new self(array_map(function ($message) {
            if ($message instanceof Message) {
                return $message;
            }

            return new Message(
                $message['role'],
                $message['content'],
                $message['name'] ?? null
            );
        }, $messages));
    }

    /**
     * Add a message to the collection.
     */
    public function add(Message|array $message): self
    {
        if (is_array($message)) {
            $message = new Message(
                $message['role'],
                $message['content'],
                $message['name'] ?? null
            );
        }

        $this->messages[] = $message;

        return $this;
    }

    /**
     * Add a user message.
     */
    public function user(string $content, ?string $name = null): self
    {
        return $this->add(Message::user($content, $name));
    }

    /**
     * Add an assistant message.
     */
    public function assistant(string $content, ?string $name = null): self
    {
        return $this->add(Message::assistant($content, $name));
    }

    /**
     * Add a system message.
     */
    public function system(string $content): self
    {
        return $this->add(Message::system($content));
    }

    /**
     * Get all messages.
     *
     * @return array<Message>
     */
    public function all(): array
    {
        return $this->messages;
    }

    /**
     * Convert all messages to array format.
     *
     * @return array<array>
     */
    public function toArray(): array
    {
        return array_map(fn (Message $message) => $message->toArray(), $this->messages);
    }

    /**
     * Check if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->messages);
    }

    /**
     * Get the number of messages.
     */
    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * Get an iterator for the messages.
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->messages);
    }

    /**
     * Check if an offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->messages[$offset]);
    }

    /**
     * Get a message at the given offset.
     */
    public function offsetGet(mixed $offset): ?Message
    {
        return $this->messages[$offset] ?? null;
    }

    /**
     * Set a message at the given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->add($value);
        } else {
            if (is_array($value)) {
                $value = new Message($value['role'], $value['content'], $value['name'] ?? null);
            }

            $this->messages[$offset] = $value;
        }
    }

    /**
     * Unset a message at the given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->messages[$offset]);
    }

    /**
     * Convert the collection to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
