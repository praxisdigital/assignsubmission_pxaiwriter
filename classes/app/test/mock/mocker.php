<?php

namespace assignsubmission_pxaiwriter\app\test\mock;


use PHPUnit\Framework\MockObject\MockObject;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

abstract class mocker
{
    protected array $mocks = [];
    protected array $mock_callbacks = [];

    public function set_mock_callback(string $method, callable $callback): void
    {
        $this->validate_method($method);
        $this->mock_callbacks[$method] = $callback;
    }

    /**
     * @param string $method
     * @param mixed $mock
     * @return void
     */
    public function set_mock_method(string $method, $mock): void
    {
        $this->validate_method($method);
        $this->mocks[$method] = $mock;
    }

    protected function has_mock(string $method): bool
    {
        return isset($this->mock_callbacks[$method]) || isset($this->mocks[$method]);
    }

    protected function call_mock_method(string $method, ...$args)
    {
        if (isset($this->mock_callbacks[$method]))
        {
            return $this->mock_callbacks[$method](...$args);
        }
        if (isset($this->mocks[$method]))
        {
            return $this->mocks[$method];
        }
        throw new \InvalidArgumentException("Undefined method: $method");
    }

    private function validate_method(string $method): void
    {
        if (!method_exists($this, $method))
        {
            throw new \InvalidArgumentException("Undefined method: $method");
        }
    }
}
