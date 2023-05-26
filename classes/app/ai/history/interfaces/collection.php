<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


use assignsubmission_pxaiwriter\app\interfaces\collection as base_collection;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

/**
 * @extends base_collection<entity>
 * @method entity|null last()
 * @method entity current()
 * @method base_collection<entity>|array<int, entity>|entity[] skip(int $count)
 */
interface collection extends base_collection
{
    /**
     * @return entity[][]|array<int, entity[]>
     */
    public function to_step_array(bool $reset = false): array;

    /**
     * @return int[]
     */
    public function get_step_numbers(): array;

    /**
     * @param int $step
     * @return collection<entity>|array<int,entity>|entity[]
     */
    public function get_step_entities(int $step): collection;

    public function get_first_entity_by_step(int $step): ?entity;

    public function get_latest_entity_by_step(int $step): ?entity;

    public function get_latest_history_ids(): array;
    public function get_latest_history_ids_include_substeps(): array;
}
