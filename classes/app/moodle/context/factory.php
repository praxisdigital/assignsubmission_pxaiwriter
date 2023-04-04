<?php

namespace assignsubmission_pxaiwriter\app\moodle\context;


use context_module;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    public function course_module(int $id): context_module
    {
        return context_module::instance($id);
    }
}
