<?php

namespace assignsubmission_pxaiwriter\app\helper\diff\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface text
{
    public function diff(string $old_data, string $new_data): string;

    public function set_deletion_tag(html $tag): text;

    public function set_insertion_tag(html $tag): text;
}
