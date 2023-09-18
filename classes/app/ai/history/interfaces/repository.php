<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


use cm_info;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface repository
{
    public const TABLE = 'pxaiwriter_history';

    public function count_ai_generate_text_attempts(
        int $user_id,
        int $assignment_id,
        int $from_time,
        int $to_time
    ): int;

    public function count_by_user_submission(int $user_id, int $assignment_id, int $submission_id, ?int $step = null): int;

    public function get_by_hashcode(
        int $user_id,
        int $assignment_id,
        string $hashcode,
        int $step = 1
    ): ?entity;

    public function get_last_by_ids(array $ids): ?entity;

    public function get_latest_by_submission(object $submission): ?entity;

    /**
     * @param int $user_id
     * @param int $assignment_id
     * @param int $offset
     * @param int $limit
     * @return collection<entity>|entity[]
     */
    public function get_all_by_user_assignment(
        int $user_id,
        int $assignment_id,
        int $offset = 0,
        int $limit = 0
    ): collection;

    public function get_all_by_submission(
        int $submission_id,
        int $user_id = 0,
        int $offset = 0,
        int $limit = 0
    ): collection;

    public function get_all_submitted_by_submission(
        int $submission_id,
        int $user_id = 0,
        int $assignment_id = 0,
        int $offset = 0,
        int $limit = 0
    ): collection;

    /**
     * @param array $ids
     * @return collection<entity>|entity[]
     */
    public function get_all_by_ids(array $ids): collection;

    /**
     * @param int $submission_id
     * @param int $assignment_id
     * @param int $user_id
     * @return collection<entity>|entity[]
     */
    public function get_all_drafted_by_submission(
        int $submission_id,
        int $assignment_id = 0,
        int $user_id = 0
    ): collection;

    /**
     * @param int $assignment_id
     * @param int[] $submission_ids
     * @return collection<entity>|entity[]
     */
    public function get_all_by_assign_submission(
        int $assignment_id,
        array $submission_ids
    ): collection;

    public function get_cm_info_by_history(entity $history): cm_info;

    public function insert(entity $entity): void;

    public function update(entity $entity): void;

    public function delete_by_id(int $id): void;
    public function delete_by_user_id(int $user_id): void;
    public function delete_by_user_assignment(int $user_id, int $assignment_id): void;
    public function delete_by_assignment_id(int $assignment_id): void;
    public function delete_by_assign_submission(int $assignment_id, int $submission_id): void;
}
