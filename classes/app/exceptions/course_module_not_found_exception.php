<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


use assignsubmission_pxaiwriter\app\factory;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class course_module_not_found_exception extends plugin_exception
{
    public static function from_assignment_id(int $assignment_id, ?Exception $exception = null): self
    {
        return new self(
            factory::make()->moodle()->get_string('error_course_module_not_found_by_assign_id', $assignment_id),
            0,
            $exception
        );
    }
}
