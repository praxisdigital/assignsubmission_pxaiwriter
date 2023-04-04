<?php

namespace assignsubmission_pxaiwriter\app\assign\interfaces;


use cm_info;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface repository
{
    public function get_course_module_id_by_assign_id(int $assignment_id): int;
}
