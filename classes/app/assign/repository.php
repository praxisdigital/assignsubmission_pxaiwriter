<?php

namespace assignsubmission_pxaiwriter\app\assign;


use assignsubmission_pxaiwriter\app\exceptions\course_module_not_found_exception;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use cm_info;
use dml_exception;
use moodle_exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class repository implements interfaces\repository
{
    private base_factory $factory;
    /** @var interfaces\entity[] */
    private array $assign_records = [];

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    private function get_table(): string
    {
        return 'assign';
    }

    public function is_overdue(int $assignment_id): bool
    {
        $entity = $this->get_assign_by_id($assignment_id);
        $current_time = $this->factory->helper()->times()->current_time();
        return $entity->get_duedate() < $current_time;
    }

    public function get_course_module_id_by_assign_id(int $assignment_id): int
    {
        return $this->get_course_module_by_assign_id($assignment_id)->id;
    }

    private function get_course_module_by_assign_id(int $id): cm_info
    {
        $entity = $this->get_assign_by_id($id);
        $course_module = $entity->get_course_module();

        if ($course_module === null)
        {
            throw course_module_not_found_exception::from_assignment_id($id);
        }

        return $course_module;
    }

    private function get_assign_by_id(int $id): interfaces\entity
    {
        if (!isset($this->assign_records[$id]))
        {
            $sql = "SELECT a.*, cm.id cmid FROM {{$this->get_table()}} a
                JOIN {course_modules} cm ON cm.instance = a.id
                    AND cm.deletioninprogress = 0
                    AND cm.module = (SELECT m.id FROM {modules} m WHERE m.name = :module)
                WHERE a.id = :id";

            $record = $this->factory->moodle()->db()->get_record_sql($sql, [
                'id' => $id,
                'module' => $this->get_table()
            ], MUST_EXIST);
            $record->cmid = (int)$record->cmid;

            if (!isset($this->course_modules[$record->cmid]))
            {
                $mod_info = $this->factory->moodle()->mod_info($record->course);
                $record->course_module = $mod_info->get_cm($record->cmid);
            }

            $this->assign_records[$id] = $this->factory->assign()->mapper()->map($record);
        }

        return $this->assign_records[$id];
    }
}
