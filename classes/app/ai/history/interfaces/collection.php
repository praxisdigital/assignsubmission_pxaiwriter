<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


use assignsubmission_pxaiwriter\app\interfaces\collection as base_collection;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

/**
 * @extends base_collection<entity>
 */
interface collection extends base_collection
{
    /**
     * @return entity[][]
     */
    public function to_step_array(): array;
}
