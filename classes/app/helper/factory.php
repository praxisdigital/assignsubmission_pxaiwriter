<?php

namespace assignsubmission_pxaiwriter\app\helper;


use assignsubmission_pxaiwriter\app\helper\diff\interfaces\factory as diff_factory;
use assignsubmission_pxaiwriter\app\helper\encoding\interfaces\factory as encoding_factory;
use assignsubmission_pxaiwriter\app\helper\hash\interfaces\factory as hash_factory;
use assignsubmission_pxaiwriter\app\helper\times\interfaces\factory as times_factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private array $factories = [];
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function diff(): diff_factory
    {
        return $this->factories[__FUNCTION__] ??= new diff\factory($this->factory);
    }

    public function hash(): hash_factory
    {
        return $this->factories[__FUNCTION__] ??= new hash\factory();
    }

    public function encoding(): encoding_factory
    {
        return $this->factories[__FUNCTION__] ??= new encoding\factory();
    }

    public function times(): times_factory
    {
        return $this->factories[__FUNCTION__] ??= new times\factory($this->factory);
    }
}
