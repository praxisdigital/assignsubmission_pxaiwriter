<?php

namespace assignsubmission_pxaiwriter\app\submission;


use assign;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use assignsubmission_pxaiwriter\app\submission\interfaces\entity as submission_entity;
use assignsubmission_pxaiwriter\event\assessable_uploaded;
use assignsubmission_pxaiwriter\event\submission_created;
use assignsubmission_pxaiwriter\event\submission_updated;
use context;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once $CFG->dirroot . '/mod/assign/locallib.php';

class event implements interfaces\event
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function created(
        assign $assign,
        object $submission,
        submission_entity $submission_entity
    ): void
    {
        $params = $this->get_submission_event_params_with_file_event(
            $assign,
            $submission,
            $submission_entity
        );
        $event = submission_created::create($params);
        $this->factory->assign()->mapper()->add_assign_to_event($event, $assign);
        $event->trigger();
    }

    public function updated(
        assign $assign,
        object $submission,
        submission_entity $submission_entity
    ): void
    {
        $params = $this->get_submission_event_params_with_file_event(
            $assign,
            $submission,
            $submission_entity
        );
        $event = submission_updated::create($params);
        $this->factory->assign()->mapper()->add_assign_to_event($event, $assign);
        $event->trigger();
    }

    private function get_default_params(
        context $context,
        assign $assign,
        object $submission
    ): array
    {
        return [
            'context' => $context,
            'courseid' => $assign->get_course()->id,
            'objectid' => $submission->id,
        ];
    }

    private function trigger_file_uploaded(array $params, assign $assign, object $submission): void
    {
        $params = $this->add_related_user($params, $submission);
        $params = $this->add_anonymous($params, $assign);

        $files = $this->factory->file()->repository()->get_submission_files($assign->get_context(), $submission->id);
        $params = $this->add_files($params, $files);

        $event = assessable_uploaded::create($params);
        $event->trigger();
    }

    private function get_submission_event_params_with_file_event(
        assign $assign,
        object $submission,
        submission_entity $entity
    ): array
    {
        $params = $this->get_default_params(
            $assign->get_context(),
            $assign,
            $submission
        );
        $this->trigger_file_uploaded($params, $assign, $submission);
        $params = $this->add_related_user_by_submission($params, $submission);
        $params['objectid'] = $entity->get_id();
        return $this->add_submission_event_params($params, $submission);
    }

    private function add_submission_event_params(
        array $params,
        object $submission
    ): array
    {
        $group_id = $this->get_group_id_by_submission($submission);
        $group_name = $this->get_group_name_by_group_id($group_id);
        $params['other'] = [
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'groupid' => $group_id,
            'groupname' => $group_name
        ];
        return $params;
    }

    private function add_related_user_by_submission(array $params, object $submission): array
    {
        if (!empty($submission->userid))
        {
            $params['relateduserid'] = (int)$submission->userid;
        }
        return $params;
    }

    private function add_related_user(array $params, object $submission): array
    {
        if (empty($submission->userid))
        {
            return $params;
        }

        $submission_user_id = (int)$submission->userid;
        $current_user_id = (int)$this->factory->moodle()->user()->id;
        if ($submission_user_id === $current_user_id)
        {
            return $params;
        }

        $params['relateduserid'] = $submission_user_id;
        return $params;
    }

    private function add_anonymous(array $params, assign $assign): array
    {
        if ($assign->is_blind_marking())
        {
            $params['anonymous'] = 1;
        }
        return $params;
    }

    private function add_files(array $params, array $files): array
    {
        $params['other']['pathnamehashes'] = array_keys($files);
        $params['other']['content'] = '';
        $params['other']['format'] = FORMAT_MOODLE;
        return $params;
    }

    private function get_group_id_by_submission(object $submission): int
    {
        if (!empty($submission->userid))
        {
            return 0;
        }
        if (empty($submission->groupid))
        {
            return 0;
        }
        return $submission->groupid;
    }

    private function get_group_name_by_group_id(int $group_id): ?string
    {
        if ($group_id < 1)
        {
            return null;
        }
        return $this->factory->moodle()->get_group_name_by_id($group_id);
    }
}
