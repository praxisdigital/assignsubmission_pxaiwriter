<?php

namespace assignsubmission_pxaiwriter\event;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
use assignsubmission_pxaiwriter\app\factory;
use core\event\base;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

abstract class history_record_event extends base
{
    abstract protected function get_crud_type(): string;

    protected function init()
    {
        $this->data['crud'] = $this->get_crud_type();
        $this->data['edulevel'] = $this->get_edu_level();
        $this->data['objecttable'] = $this->get_object_table();
    }

    protected function get_edu_level(): int
    {
        return self::LEVEL_PARTICIPATING;
    }

    protected function get_object_table(): string
    {
        return 'pxaiwriter_history';
    }

    protected function get_assignment_id(): int
    {
        return $this->other['assignment'] ?? 0;
    }

    protected function get_step(): int
    {
        return $this->other['step'] ?? 0;
    }

    protected function get_submission_id(): int
    {
        return $this->other['submission'] ?? 0;
    }

    protected function get_user_id(): int
    {
        return $this->relateduserid ?? $this->userid;
    }

    public static function create_from_history(entity $history): self
    {
        $course_module = factory::make()
            ->ai()
            ->history()
            ->repository()
            ->get_cm_info_by_history($history);

        return self::create([
            'objectid' => $history->get_id(),
            'context' => $course_module->context,
            'courseid' => $course_module->course,
            'userid' => factory::make()->moodle()->user()->id,
            'relateduserid' => $history->get_userid(),
            'other' => [
                'assignment' => $history->get_assignment(),
                'submission' => $history->get_submission(),
                'userid' => $history->get_userid(),
                'step' => $history->get_step(),
                'status' => $history->get_status(),
                'checksum' => $history->get_hashcode(),
                'input_text' => $history->get_input_text(),
            ],
        ]);
    }

    public static function trigger_by_history(entity $history): void
    {
        try
        {
            self::create_from_history($history)->trigger();
        }
        catch (Exception $exception) {}
    }
}
