<?php

namespace assignsubmission_pxaiwriter\app\setting\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */


interface settings
{
    public function get_attempt_count(): int;
    public function get_system_message(): string;
}
