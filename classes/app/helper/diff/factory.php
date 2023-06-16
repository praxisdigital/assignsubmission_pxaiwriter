<?php

namespace assignsubmission_pxaiwriter\app\helper\diff;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private array $instances = [];
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function text(): interfaces\text
    {
        return $this->instances[__FUNCTION__] ??= new text($this->factory);
    }
}
