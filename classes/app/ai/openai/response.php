<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class response implements interfaces\response
{
    private string $json;
    private string $text;

    public function __construct(string $json, string $text)
    {
        $this->json = $json;
        $this->text = $text;
    }
    public function get_text(): string
    {
        return $this->text;
    }
    public function get_response_json(): string
    {
        return $this->json;
    }
    public function __toString()
    {
        return $this->get_text();
    }
}
