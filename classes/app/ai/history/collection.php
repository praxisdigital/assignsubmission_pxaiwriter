<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
use assignsubmission_pxaiwriter\app\collection as base_collection;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

/**
 * @extends base_collection<interfaces\entity>
 */
class collection extends base_collection implements interfaces\collection
{

    /**
     * @param interfaces\entity[] $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function to_step_array(): array
    {
        $entities = [];
        foreach ($this->items as $entity)
        {
            $entities[$entity->get_step()][] = $entity;
        }
        return $entities;
    }
}
