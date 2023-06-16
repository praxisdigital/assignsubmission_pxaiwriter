<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface data
{
    public function get_attempt_text(): string;
    public function get_attempted_count(): int;
    public function get_max_attempts(): int;
    public function is_exceeded(): bool;

    public function make_attempt(): void;
}
