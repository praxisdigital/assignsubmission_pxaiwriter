<?php

namespace assignsubmission_pxaiwriter\app\assign\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface mapper
{
    public function map(?object $record = null): entity;
}
