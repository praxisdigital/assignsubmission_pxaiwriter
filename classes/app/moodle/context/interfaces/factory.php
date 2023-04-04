<?php

namespace assignsubmission_pxaiwriter\app\moodle\context\interfaces;


use context_module;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function course_module(int $id): context_module;
}
