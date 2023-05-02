<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


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

    /**
     * @param array $ids
     * @return collection<entity>|entity[]
     */
    public function get_all_by_ids(array $ids): collection;

    public function insert(entity $entity): void;
    public function delete_by_id(int $id): void;
    public function delete_by_user_id(int $user_id): void;
    public function delete_by_user_assignment(int $user_id, int $assignment_id): void;
}
