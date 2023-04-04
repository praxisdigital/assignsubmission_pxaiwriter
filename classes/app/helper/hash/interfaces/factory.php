<?php

namespace assignsubmission_pxaiwriter\app\helper\hash\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function sha256(): hash_function;
}
