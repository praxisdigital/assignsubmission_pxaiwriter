<?php

namespace assignsubmission_pxaiwriter\app;


use JsonSerializable;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

/**
 * @template T
 * @psalm-template T
 * @implements interfaces\collection<T>
 */
class collection implements interfaces\collection, JsonSerializable
{
    /** @var T[] */
    protected array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function next(): void
    {
        next($this->items);
    }

    public function key(): mixed
    {
        return key($this->items);
    }

    public function valid(): bool
    {
        return $this->offsetExists($this->key());
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return T
     */
    public function current(): mixed
    {
        return current($this->items);
    }

    public function last(): mixed
    {
        if (empty($this->items))
        {
            return null;
        }
        $last_index = array_key_last($this->items);
        return $this->offsetGet($last_index);
    }

    public function skip(int $count): interfaces\collection
    {
        $items = array_slice($this->items, $count);
        return new static($items);
    }

    /**
     * @param $offset
     * @return T
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function jsonSerialize(): array
    {
        return $this->to_array();
    }

    public function to_array(): array
    {
        return $this->items;
    }
}
