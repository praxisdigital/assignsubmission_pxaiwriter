<?php

namespace assignsubmission_pxaiwriter\app\ai\openai\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

interface chat_response
{
    public function get_message(): string;
    public function get_as_json(): string;
    public function get_data(): array;
    public function get_previous_response(): ?chat_response;

    /**
     * @return chat_response[]
     */
    public function get_responses(): array;

    public function set_previous_response(chat_response $response): void;

    public function is_finished(): bool;
}
