<?php

namespace assignsubmission_pxaiwriter\app\assign;

use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class mapper implements interfaces\mapper
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }
    
    public function map(?object $record = null): interfaces\entity
    {
        return $this->factory->assign()->entity((array)$record);
    }
}
