<?php

namespace assignsubmission_pxaiwriter\app\ai\openai\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface api
{
    public function generate_ai_text(string $user_text): string;

    public function expand_ai_text(string $user_text): string;
}
