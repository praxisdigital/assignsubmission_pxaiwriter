<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class mapper implements interfaces\mapper
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function map(object $record): entity
    {
        return $this->factory->ai()->history()->entity((array)$record);
    }

    public function map_collection(iterable $records): interfaces\collection
    {
        $entities = [];
        foreach ($records as $id => $record)
        {
            $entities[$id] = $this->map($record);
        }
        return new collection($entities);
    }
}
