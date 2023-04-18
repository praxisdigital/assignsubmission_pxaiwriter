<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class openai_api_response_exception extends plugin_exception
{
    public static function api_response(string $message): self
    {
        $error_message = "OpenAI API response error: $message";
        return new self($error_message, 0);
    }

    public static function no_choice_in_response(): self
    {
        $chat_api_link = self::get_chat_api_link();
        $completion_api_link = self::get_text_completion_api_link();
        $message = 'Undefined choice in the API responses.';
        $message .= " The response data must follow $chat_api_link or $completion_api_link";
        return new self($message, 1);
    }

    public static function by_chat_completion(): self
    {
        $api_link = self::get_chat_api_link();
        $message = "Undefined message property in the API response.";
        $message .= " The response data must follow $api_link";
        return new self($message, 2);
    }

    public static function by_text_completion(): self
    {
        $api_link = self::get_text_completion_api_link();
        $message = "Undefined text property in the API response.";
        $message .= " The response data must follow $api_link";
        return new self($message, 3);
    }

    private static function get_chat_api_link(): string
    {
        return 'https://platform.openai.com/docs/api-reference/chat/create';
    }

    private static function get_text_completion_api_link(): string
    {
        return 'https://platform.openai.com/docs/api-reference/completions/create';
    }
}
