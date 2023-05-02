<?php

namespace assignsubmission_pxaiwriter\app\submission\interfaces;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\collection as history_collection;
use assignsubmission_pxaiwriter\app\interfaces\collection;
use context;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface submission_history
{
    public function get_context(): context;

    public function get_submission(): object;

    /**
     * @return collection<step_config>|array<int,step_config>|step_config[]
     */
    public function get_steps_config(): collection;

    public function get_history_list(bool $reset = false): history_collection;

    public function get_step_config(int $step): ?step_config;
}
