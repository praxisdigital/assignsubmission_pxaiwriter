<?php

namespace assignsubmission_pxaiwriter\app\ai\openai\interfaces;


use assignsubmission_pxaiwriter\app\setting\interfaces\settings;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface mapper
{
    public function map_request(string $text, settings $settings): array;

    public function map_response(string $json, string $model): string;
}
