<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface mapper
{
    /**
     * @param object $record
     * @return entity
     */
    public function map(object $record): entity;

    /**
     * @param iterable<object>|object[] $records
     * @return collection<entity>
     */
    public function map_collection(iterable $records): collection;
}
