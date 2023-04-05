<?php

namespace assignsubmission_pxaiwriter\app\assign;


use assignsubmission_pxaiwriter\app\entity as base_entity;
use cm_info;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class entity extends base_entity implements interfaces\entity
{
    public function to_array(): array
    {
        return $this->record;
    }

    public function to_object(): object
    {
        return (object)$this->to_array();
    }

    public function get_course(): int
    {
        return $this->record['course'] ?? 0;
    }

    public function get_duedate(): int
    {
        return $this->record['duedate'] ?? 0;
    }

    public function get_cmid(): int
    {
        return $this->record['cmid'] ?? 0;
    }

    public function get_course_module(): ?cm_info
    {
        return $this->record['course_module'] ?? null;
    }
}
