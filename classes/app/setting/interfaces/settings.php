<?php

namespace assignsubmission_pxaiwriter\app\setting\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */


interface settings
{
    public function get_default(): bool;
    public function get_presence_penalty(): float;
    public function get_frequency_penalty(): float;
    public function get_top_p(): float;
    public function get_max_tokens(): int;
    public function get_temperature(): float;

    public function get_model(): string;
    public function get_openai_token(): string;
    public function get_url(): string;

    public function get_attempt_count(): int;
    public function get_system_message(): string;
}
