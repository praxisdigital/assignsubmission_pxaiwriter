<?php

namespace assignsubmission_pxaiwriter\app\test\webservice;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class generate_ai_text_response
{
    private array $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function get_data(): string
    {
        return $this->response['data'] ?? '';
    }

    public function get_attempt_text(): string
    {
        return $this->response['attempt_text'] ?? '';
    }

    public function get_attempted_count(): int
    {
        return 0;
    }

    public function get_max_attempt(): int
    {
        return 0;
    }
}
