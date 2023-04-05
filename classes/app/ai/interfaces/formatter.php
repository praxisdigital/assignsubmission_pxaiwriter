<?php

namespace assignsubmission_pxaiwriter\app\ai\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface formatter
{
    public function text(string $input_text, string $ai_text): string;
    public function replace(string $original_text, string $input_text, string $ai_text, int $offset): string;
}
