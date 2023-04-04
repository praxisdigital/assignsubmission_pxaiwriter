<?php

namespace assignsubmission_pxaiwriter\app\assign;


use assignsubmission_pxaiwriter\app\exceptions\course_module_not_found_exception;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use cm_info;
use dml_exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class repository implements interfaces\repository
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function get_course_module_id_by_assign_id(int $assignment_id): int
    {
        try
        {
            $sql = "SELECT id FROM {course_modules}
                WHERE instance=:assign
                    AND module=(SELECT id FROM {modules} WHERE name='assign')
                    AND deletioninprogress = 0";
            $course_module_id = (int)$this->factory->moodle()->db()->get_field_sql($sql, [
                'assign' => $assignment_id
            ], MUST_EXIST);

            if ($course_module_id > 0)
            {
                return $course_module_id;
            }
        }
        catch (dml_exception $exception)
        {
            throw course_module_not_found_exception::from_assignment_id($assignment_id, $exception);
        }

        throw course_module_not_found_exception::from_assignment_id($assignment_id);
    }
}
