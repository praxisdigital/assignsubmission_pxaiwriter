<?php

namespace assignsubmission_pxaiwriter\event;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class history_record_updated extends history_record_event
{
    protected function get_crud_type(): string
    {
        return 'u';
    }

    public function get_description()
    {
        $description = "User with id '{$this->get_user_id()}'";
        $description .= " updated AI writer history record with id '{$this->objectid}'";
        $description .= " at the step number '{$this->get_step()}'";
        $description .= " in the submission with id '{$this->get_submission_id()}'";
        $description .= " for the assignment with id '{$this->get_assignment_id()}'";
        return $description;
    }
}
