<?php

namespace assignsubmission_pxaiwriter\app\assign\interfaces;


use cm_info;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface entity
{
    public function get_id(): int;
    public function get_course(): int;
    public function get_duedate(): int;
    public function get_cmid(): int;
    public function get_course_module(): ?cm_info;
}
