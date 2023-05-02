<?php

namespace assignsubmission_pxaiwriter\app\ai\openai\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function api(): api;
    public function mapper(): mapper;
    public function models(): models;
    public function response(string $json, string $text): response;
}
