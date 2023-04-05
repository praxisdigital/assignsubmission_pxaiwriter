<?php

namespace assignsubmission_pxaiwriter\app\test\mock;


use PHPUnit\Framework\MockObject\MockObject;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

abstract class mocker
{
    protected array $mocks = [];

    public function set_mock_method(string $method, object $mock): void
    {
        if (!method_exists($this, $method))
        {
            throw new \InvalidArgumentException("Undefined method: $method");
        }
        $this->mocks[$method] = $mock;
    }
}
