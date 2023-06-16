<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class submission_instance
{
    private base_factory $factory;
    private object $record;

    public function __construct(
        base_factory $factory,
        object $record
    )
    {
        $this->factory = $factory;
        $this->record = $record;
    }

    public function get_id(): int
    {
        return $this->record->id;
    }

    public function get_assignment(): int
    {
        return $this->record->assignment;
    }

    public function get_submission(): int
    {
        return $this->record->submission;
    }

    public function get_userid(): int
    {
        return $this->record->userid;
    }

    public function get_steps_data(): steps_data
    {
        return $this->record->steps_data;
    }

    public function set_steps_data(array $data): void
    {
        $this->record->steps_data = $data;
    }

    public function to_record(): object
    {
        $record = (array)$this->record;
        $record['steps_data'] = $this->factory->helper()
            ->encoding()
            ->json()
            ->encode($this->get_steps_data());
        return (object)$record;
    }
}
