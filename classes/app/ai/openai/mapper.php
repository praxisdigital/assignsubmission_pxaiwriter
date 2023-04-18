<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;


use assignsubmission_pxaiwriter\app\exceptions\openai_api_response_exception;
use assignsubmission_pxaiwriter\app\exceptions\unsupported_openai_model_exception;
use assignsubmission_pxaiwriter\app\interfaces\factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\settings;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class mapper implements interfaces\mapper
{
    private factory $factory;

    public function __construct(factory $factory)
    {
        $this->factory = $factory;
    }

    public function map_response(string $json, string $model): string
    {
        $response = $this->factory->helper()->encoding()->json()->decode_as_array($json);
        $this->throw_on_error_response($response);

        switch ($model) {
            case interfaces\models::GPT_3_5_TURBO:
                return $this->get_chat_message($response);
            case interfaces\models::TEXT_DAVINCI_2:
            case interfaces\models::TEXT_DAVINCI_3:
                return $this->get_text_completion($response);
            default:
                throw unsupported_openai_model_exception::by_model_type($model);
        }
    }

    public function map_request(string $text, settings $settings): array
    {
        $model = $settings->get_model();

        switch ($model)
        {
            case interfaces\models::GPT_3_5_TURBO:
                return $this->get_chat_completion_request($text, $settings);
            case interfaces\models::TEXT_DAVINCI_2:
            case interfaces\models::TEXT_DAVINCI_3:
                return $this->get_text_completion_request($text, $settings);
            default:
                throw unsupported_openai_model_exception::by_model_type($model);
        }
    }

    private function get_text_completion_request(string $text, settings $settings): array
    {
        return [
            'model' => $settings->get_model(),
            'prompt' => $text,
            'temperature' => $settings->get_temperature(),
            'max_tokens' => $settings->get_max_tokens(),
            'top_p' => $settings->get_top_p(),
            'frequency_penalty' => $settings->get_frequency_penalty(),
            'presence_penalty' => $settings->get_presence_penalty()
        ];
    }

    private function get_chat_completion_request(string $text, settings $settings): array
    {
        return [
            'model' => $settings->get_model(),
            'messages' => [
                $this->get_chat_completion_system_role_message($text),
                $this->get_chat_completion_user_role_message($text),
            ],
            'temperature' => $settings->get_temperature(),
            'max_tokens' => $settings->get_max_tokens(),
            'top_p' => $settings->get_top_p(),
            'frequency_penalty' => $settings->get_frequency_penalty(),
            'presence_penalty' => $settings->get_presence_penalty()
        ];
    }

    private function get_chat_completion_system_role_message(string $message): array
    {
        return [
            'role' => 'system',
            'content' => $message
        ];
    }

    private function get_chat_completion_user_role_message(string $message): array
    {
        return [
            'role' => 'user',
            'content' => $message
        ];
    }

    private function get_first_choice(array $response): array
    {
        if (!isset($response['choices'][0]))
        {
            throw openai_api_response_exception::no_choice_in_response();
        }
        return $response['choices'][0];
    }

    /**
     * Get chat message <br>
     * Support interfaces\models: gpt-3.5-turbo, gpt-4, gpt-4-32k
     * @param array $response
     * @return string
     * @throws openai_api_response_exception
     */
    private function get_chat_message(array $response): string
    {
        $choice = $this->get_first_choice($response);
        if (!isset($choice['message']['content']))
        {
            throw openai_api_response_exception::by_chat_completion();
        }
        return $choice['message']['content'];
    }

    /**
     * Get text completion <br>
     * Support interfaces\models: text-davinci-003, text-davinci-002
     * @param array $response
     * @return string
     * @throws openai_api_response_exception
     */
    private function get_text_completion(array $response): string
    {
        $choice = $this->get_first_choice($response);
        if (!isset($choice['text']))
        {
            throw openai_api_response_exception::by_text_completion();
        }
        return $choice['text'];
    }

    private function throw_on_error_response(array $response): void
    {
        if (isset($response['error']))
        {
            throw openai_api_response_exception::api_response($response['error']['message'] ?? '');
        }
    }
}
