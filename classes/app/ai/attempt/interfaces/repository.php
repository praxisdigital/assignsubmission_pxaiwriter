<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface repository
{
    public function count_success_attempt_in_timespan(
        int $user_id,
        int $assignment_id,
        int $from_time,
        int $to_time
    ): int;

    public function get_remaining_attempt(
        int $user_id,
        int $assignment_id,
        int $from_time,
        int $to_time,
        ?int $max_attempts = null
    ): data;

    public function get_today_remaining_attempt(
        int $user_id,
        int $assignment_id,
        ?int $max_attempts = null
    ): data;

    public function insert(entity $entity): void;

    public function delete(entity $entity): void;
    public function delete_by_id(string $id): void;
    public function delete_by_user_id(string $user_id): void;
    public function delete_by_assignment_id(string $assignment_id): void;
}
