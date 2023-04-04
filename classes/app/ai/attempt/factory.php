<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt;


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

    public function entity(array $record = []): interfaces\entity
    {
        return new entity($record);
    }

    public function mapper(): interfaces\mapper
    {
        return $this->instances[__FUNCTION__] ??= new mapper($this->factory);
    }

    public function repository(): interfaces\repository
    {
        return $this->instances[__FUNCTION__] ??= new repository($this->factory);
    }
}
