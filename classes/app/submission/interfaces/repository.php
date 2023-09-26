<?php

namespace assignsubmission_pxaiwriter\app\submission\interfaces;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\collection as history_collection;
use context;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();

/* @codeCoverageIgnoreEnd */
interface repository
{
    public function get_submission_history(
        context $context,
        object $submission,
        object $config,
        ?history_collection $history_list = null
    ): submission_history;

    public function get_step_data_json(array $steps_data): string;
    public function get_step_data_by_assign_submission(object $submission, object $submission_config, context $context): array;

    public function get_step_data_by_form_data(object $data): array;

    public function add_ai_writer_submission_data(
        object $submission,
        object $submission_data,
        array $steps_data,
        ?int $duedate = null
    ): object;

    public function get_by_assign_submission(object $submission): ?entity;

    public function has_id(int $submission_id): bool;

    public function create_by_submission_history(submission_history $submission_history): entity;

    public function update_by_submission_history(entity $entity, submission_history $submission_history): void;

    public function save_data(object $submission, object $submission_data): void;

    public function delete_by_submission(object $submission): void;

    public function delete_by_assignment_id(int $assignment_id): void;

    public function delete_by_assign_submission(int $assignment_id, int $submission_id): void;

    public function copy_to(entity $source, int $destination_submission_id): void;

    public function insert(entity $entity): void;
    public function update(entity $entity): void;
}
