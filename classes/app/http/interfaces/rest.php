<?php

namespace assignsubmission_pxaiwriter\app\http\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface rest
{
    public function header(): header;
    public function post(string $url, array $params = []): response;
}
