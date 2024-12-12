<?php

namespace assignsubmission_pxaiwriter\app\submission;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\collection as history_collection;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity as history_entity;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use assignsubmission_pxaiwriter\app\submission\interfaces\entity;
use context;
use Exception;
use moodle_database;
use moodle_exception;

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

    private function db(): moodle_database
    {
        return $this->factory->moodle()->db();
    }

    private function get_table(): string
    {
        return 'assignsubmission_pxaiwriter';
    }

    public function get_submission_history(
        context $context,
        object $submission,
        object $config,
        ?history_collection $history_list = null
    ): interfaces\submission_history
    {
        return new submission_history(
            $this->factory,
            $context,
            $submission,
            $config,
            $history_list
        );
    }

    public function get_by_assign_submission(object $submission): ?interfaces\entity
    {
        try
        {
            $record = $this->db()->get_record($this->get_table(), [
                'assignment' => $submission->assignment,
                'submission' => $submission->id,
            ], '*', MUST_EXIST);

            return $this->factory->submission()->mapper()->map($record);
        }
        catch (Exception $exception) {}

        return null;
    }

    public function get_step_data_json(array $steps_data): string
    {
        return $this->factory->helper()->encoding()->json()->encode(
            $this->factory->collection($steps_data)
        );
    }

    public function get_step_data_by_assign_submission(object $submission, object $submission_config, context $context): array
    {
        $step_configs = $this->get_ai_writer_step_configs($submission_config);

        $entity = $this->factory->submission()->repository()->get_by_assign_submission($submission);
        $history_ids = $entity ? $entity->get_history_ids() : [];
        
        $history_list = $this->factory->ai()->history()->repository()->get_all_by_ids(
            $history_ids
        );

        $steps_data = [];

        foreach ($step_configs as $config)
        {
            $step_config = new step_config($config);
            $history = $history_list->get_latest_entity_by_step($step_config->get_step());
            $step_config->set_history_data($history);
            $steps_data[] = $step_config->to_object();
        }

        return $steps_data;
    }

    public function get_step_data_by_form_data(object $data): array
    {
        if (empty($data->assignsubmission_pxaiwriter_student_data))
        {
            return [];
        }

        return $this->factory->helper()->encoding()->json()->decode(
            $data->assignsubmission_pxaiwriter_student_data
        );
    }

    public function has_id(int $submission_id): bool
    {
        return $this->db()->record_exists(
            $this->get_table(),
            [
                'submission' => $submission_id
            ]
        );
    }

    public function add_ai_writer_submission_data(
        object $submission,
        object $submission_data,
        array $steps_data,
        ?int $duedate = null
    ): object
    {
        $submission_data->assignmentid = (int)$submission->assignment;
        $submission_data->submissionid = (int)$submission->id;
        $current_time = $this->factory->helper()->times()->current_time();
        $duedate ??= 0;
        $submission_data->is_due_submission = $duedate < $current_time;
        $submission_data->enabled_ai_actions = false;

        $day = $this->factory->helper()->times()->day();
        $attempt = $this->factory->ai()->attempt()->repository()->get_remaining_attempt(
            $submission->userid,
            $submission_data->assignmentid,
            $day->get_start_of_day()->getTimestamp(),
            $day->get_end_of_day()->getTimestamp()
        );
        $submission_data->exceeds_max_attempts = $attempt->is_exceeded();
        $submission_data->attempts_count = $attempt->get_attempted_count();
        $submission_data->max_attempts = $attempt->get_max_attempts();
        $submission_data->enabled_ai_actions = !$submission_data->exceeds_max_attempts;

        $submission_data->steps_data = $steps_data;


        $attempt_data = $this->factory->ai()->attempt()->repository()->get_today_remaining_attempt(
            $submission->userid,
            $submission_data->assignmentid
        );
        $submission_data->attempt_text =  $attempt_data->get_attempt_text();

        return $submission_data;
    }

    public function create_by_submission_history(interfaces\submission_history $submission_history): entity
    {
        $submission = $submission_history->get_submission();
        $entity = $this->factory->submission()->mapper()->map();
        $entity->set_submission($submission->id);
        $entity->set_assignment($submission->assignment);

        $history_list = $submission_history->get_history_list(true);

        $entity->set_history_ids(
            $history_list->get_latest_history_ids_include_substeps()
        );
        $entity->set_latest_step_history_ids(
            $history_list->get_latest_history_ids()
        );

        $id = $this->db()->insert_record(
            $this->get_table(),
            $entity->to_object()
        );
        $entity->set_id($id);

        return $entity;
    }

    public function update_by_submission_history(entity $entity, interfaces\submission_history $submission_history): void
    {
        $history_list = $submission_history->get_history_list();
        $history_list->get_latest_history_ids();
        $entity->set_latest_step_history_ids(
            $history_list->get_latest_history_ids()
        );
        $entity->set_history_ids(
            $history_list->get_latest_history_ids_include_substeps()
        );

        $this->update($entity);
    }

    public function save_data(object $submission, object $submission_data): void
    {
        $step_data = $this->get_step_data_by_form_data($submission_data);
        $archive = $this->factory->ai()->history()->archive_user_edit(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $draft_history_list = $this->factory->ai()->history()->repository()->get_all_drafted_by_submission(
            $submission->id
        );

        foreach ($step_data as $step)
        {
            if (!isset($step->value, $step->step))
            {
                continue;
            }

            $text = (string)$step->value;
            $step_number = (int)$step->step;

            $history = $draft_history_list->get_latest_entity_by_step($step_number);
            if (!$this->is_modified($history, $text))
            {
                continue;
            }

            $archive->commit(
                $text,
                $step_number
            );
        }

        $archive->save_draft();
    }

    public function copy_to(entity $source, int $destination_submission_id): void
    {
        $instance = $source->to_object();
        unset($instance->id);
        $instance->submission = $destination_submission_id;
        $this->db()->insert_record($this->get_table(), $instance);
    }

    public function insert(interfaces\entity $entity): void
    {
        $id = $this->db()->insert_record($this->get_table(), $entity->to_object());
        $entity->set_id($id);
    }

    public function update(interfaces\entity $entity): void
    {
        $this->db()->update_record($this->get_table(), $entity->to_object());
    }

    public function delete_by_submission(object $submission): void
    {
        if (!isset($submission->id))
        {
            return;
        }

        $this->delete_history_by_submission($submission);

        $this->db()->delete_records(
            $this->get_table(),
            [
                'id' => $submission->id
            ]
        );
    }

    public function delete_by_assignment_id(int $assignment_id): void
    {
        $this->db()->delete_records_select(
            $this->get_table(),
            'assignment = :assignment',
            ['assignment' => $assignment_id]
        );
    }

    public function delete_by_assign_submission(int $assignment_id, int $submission_id): void
    {
        $this->db()->delete_records_select(
            $this->get_table(),
            'assignment = :assignment AND submission = :submission',
            [
                'assignment' => $assignment_id,
                'submission' => $submission_id
            ]
        );

        $this->factory->ai()->history()->repository()->delete_by_assign_submission(
            $assignment_id,
            $submission_id
        );
    }


    private function delete_history_by_submission(object $submission): void
    {
        if (!isset($submission->userid, $submission->assignment))
        {
            return;
        }
        $this->factory->ai()->history()->repository()->delete_by_user_assignment(
            $submission->userid,
            $submission->assignment
        );
    }

    private function get_ai_writer_step_configs(object $config): array
    {
        if (empty($config->pxaiwritersteps))
        {
            throw new moodle_exception(
                'missing_assignsubmission_config',
                base_factory::COMPONENT
            );
        }
        
        return $this->factory->helper()->encoding()->json()->decode($config->pxaiwritersteps);
    }

    private function is_modified(?history_entity $history, ?string $text): bool
    {
        if ($history === null)
        {
            if (empty($text))
            {
                return false;
            }
        }
        elseif ($history->get_data() === $text)
        {
            return false;
        }

        return true;
    }
}
