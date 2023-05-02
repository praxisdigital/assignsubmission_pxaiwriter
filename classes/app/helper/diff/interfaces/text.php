<?php

namespace assignsubmission_pxaiwriter\app\helper\diff\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface text
{
    public function diff(string $old_data, string $new_data): string;
}
