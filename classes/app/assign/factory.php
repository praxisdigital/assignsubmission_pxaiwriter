<?php

namespace assignsubmission_pxaiwriter\app\assign;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private base_factory $factory;
    private array $instances = [];

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function repository(): interfaces\repository
    {
        return $this->instances[__FUNCTION__] ??= new repository($this->factory);
    }
}
