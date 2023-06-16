<?php

namespace assignsubmission_pxaiwriter\app\submission;


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

    public function entity(array $record = []): interfaces\entity
    {
        return new entity($record, $this->factory);
    }

    public function event(): interfaces\event
    {
        return $this->instances[__FUNCTION__] ??= new event($this->factory);
    }

    public function mapper(): interfaces\mapper
    {
        return $this->instances[__FUNCTION__] ??= new mapper($this->factory);
    }

    public function repository(): interfaces\repository
    {
        return $this->instances[__FUNCTION__] ??= new repository($this->factory);
    }

    public function step_config(object $config): interfaces\step_config
    {
        return new step_config($config);
    }
}
