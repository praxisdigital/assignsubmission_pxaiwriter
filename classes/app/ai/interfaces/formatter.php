<?php

namespace assignsubmission_pxaiwriter\app\ai\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface formatter
{
    public function text(string $input_text, string $ai_text): string;
}
