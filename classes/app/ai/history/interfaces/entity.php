<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


use assignsubmission_pxaiwriter\app\interfaces\entity as base_entity;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface entity extends base_entity
{
    public const STATUS_OK = 'ok';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELETED = 'deleted';

    public function get_assignment(): int;
    public function get_userid(): int;
    public function get_step(): int;
    public function get_status(): string;
    public function get_hashcode(): string;
    public function get_data(): string;
    public function get_ai_text(): string;
    public function get_timecreated(): int;
    public function get_timemodified(): int;

    public function set_assignment(int $assignment_id): void;
    public function set_userid(int $user_id): void;
    public function set_step(int $step): void;
    public function set_status(string $status): void;
    public function set_status_ok(): void;
    public function set_status_failed(): void;
    public function set_status_deleted(): void;
    public function set_hashcode(string $hashcode): void;
    public function set_ai_text(?string $text): void;
    public function set_data(string $data): void;
    public function set_timecreated(int $timestamp): void;
    public function set_timemodified(int $timestamp): void;
}
