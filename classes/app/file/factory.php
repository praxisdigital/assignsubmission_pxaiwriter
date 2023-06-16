<?php

namespace assignsubmission_pxaiwriter\app\file;


use assignsubmission_pxaiwriter\app\file\pdf\interfaces\factory as pdf_factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private base_factory $factory;
    private array $factories = [];
    private array $instances = [];

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function pdf(): pdf_factory
    {
        return $this->factories[__FUNCTION__] ??= new pdf\factory($this->factory);
    }

    public function repository(): interfaces\repository
    {
        return $this->instances[__FUNCTION__] ??= new repository($this->factory);
    }
}
