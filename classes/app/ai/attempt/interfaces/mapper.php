<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface mapper
{
    public function map(object $record): entity;
}
