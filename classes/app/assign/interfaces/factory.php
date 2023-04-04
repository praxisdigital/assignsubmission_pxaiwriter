<?php

namespace assignsubmission_pxaiwriter\app\assign\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function repository(): repository;
}
