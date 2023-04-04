<?php

namespace assignsubmission_pxaiwriter\app\http;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class response implements interfaces\response
{
    private string $response;

    public function __construct(string $response)
    {
        $this->response = $response;
    }

    public function get_text(): string
    {
        return $this->response;
    }

    public function __toString()
    {
        return $this->get_text();
    }
}
