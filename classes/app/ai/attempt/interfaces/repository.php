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
}
