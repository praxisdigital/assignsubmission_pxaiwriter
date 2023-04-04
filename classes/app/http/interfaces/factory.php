<?php

namespace assignsubmission_pxaiwriter\app\http\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function json(?header $header = null): rest;
}
