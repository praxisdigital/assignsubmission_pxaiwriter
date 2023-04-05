<?php

namespace assignsubmission_pxaiwriter\app\http\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface response
{
    public function get_text(): string;
}
