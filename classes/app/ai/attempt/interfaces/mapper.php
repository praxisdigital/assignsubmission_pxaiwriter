<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface mapper
{
    public function map(object $record): entity;

//    /**
//     * @param iterable<entity>|entity[] $records
//     * @return collection<entity>|entity[]
//     */
//    public function map_to_collection(iterable $records): collection;
}
