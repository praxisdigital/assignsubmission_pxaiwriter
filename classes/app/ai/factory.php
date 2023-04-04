<?php

namespace assignsubmission_pxaiwriter\app\ai;


use assignsubmission_pxaiwriter\app\ai\attempt\interfaces\factory as attempt_factory;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\factory as history_factory;
use assignsubmission_pxaiwriter\app\ai\openai\api;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private array $instances = [];
    private array $factories = [];
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function api(): openai\interfaces\api
    {
        return $this->factories[__FUNCTION__] ??= new api($this->factory);
    }

    public function attempt(): attempt_factory
    {
        return $this->factories[__FUNCTION__] ??= new attempt\factory($this->factory);
    }

    public function formatter(): interfaces\formatter
    {
        return $this->instances[__FUNCTION__] ??= new formatter();
    }

    public function history(): history_factory
    {
        return $this->factories[__FUNCTION__] ??= new history\factory($this->factory);
    }
}
