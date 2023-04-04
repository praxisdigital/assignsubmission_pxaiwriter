<?php

namespace assignsubmission_pxaiwriter\app\http;


use ArrayAccess;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class header implements interfaces\header, ArrayAccess
{
    private array $headers;

    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    public function get(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    public function set(string $name, $value): void
    {
        $this->headers[$name] = $value;
    }

    public function has(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    public function delete(string $name): void
    {
        unset($this->headers[$name]);
    }

    public function clear(): void
    {
        $this->headers = [];
    }

    public function to_array(): array
    {
        return $this->headers;
    }

    public function to_http_header(): array
    {
        $http_header = [];
        foreach ($this->headers as $header => $value)
        {
            $http_header[] = "$header: $value";
        }
        return $http_header;
    }
}
