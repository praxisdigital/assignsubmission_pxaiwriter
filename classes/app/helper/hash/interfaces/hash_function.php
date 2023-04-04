<?php

namespace assignsubmission_pxaiwriter\app\helper\hash\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface hash_function
{
    public function digest(string $data): string;
}
