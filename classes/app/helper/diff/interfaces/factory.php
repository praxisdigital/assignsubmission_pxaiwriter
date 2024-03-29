<?php

namespace assignsubmission_pxaiwriter\app\helper\diff\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function text(): text;
    public function html_diff(): text;

    public function deletion_tag(): html;
    public function insertion_tag(): html;
}
