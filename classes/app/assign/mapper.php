<?php

namespace assignsubmission_pxaiwriter\app\assign;

use assign;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use core\event\base;
use mod_assign\event\base as assign_base_event;

global $CFG;
require_once $CFG->dirroot . '/mod/assign/locallib.php';

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

    public function add_assign_to_event(base $event, assign $assign): void
    {
        if ($event instanceof assign_base_event)
        {
            $event->set_assign($assign);
        }
    }
}
