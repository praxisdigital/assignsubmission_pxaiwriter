<?php

namespace assignsubmission_pxaiwriter\app\ai\openai\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface response
{
    public function get_text(): string;
    public function get_response_json(): string;
}
