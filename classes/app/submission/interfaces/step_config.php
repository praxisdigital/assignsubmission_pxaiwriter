<?php

namespace assignsubmission_pxaiwriter\app\submission\interfaces;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity as history_entity;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */
interface step_config
{
    public function get_step(): int;
    public function get_description(): string;

    public function get_value(): string;

    public function set_history_data(?history_entity $entity): void;
}
