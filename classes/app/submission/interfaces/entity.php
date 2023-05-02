<?php

namespace assignsubmission_pxaiwriter\app\submission\interfaces;


use assignsubmission_pxaiwriter\app\interfaces\entity as base_entity;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface entity extends base_entity
{
    public function get_assignment(): int;
    public function get_submission(): int;
    public function get_step_data(): ?string;
    public function get_history_ids(): array;
    public function get_latest_step_history_ids(): array;

    public function set_assignment(int $id): void;
    public function set_submission(int $id): void;
    public function set_step_data(?string $data): void;
    public function set_history_ids(array $ids): void;
    public function set_latest_step_history_ids(array $ids): void;
}
