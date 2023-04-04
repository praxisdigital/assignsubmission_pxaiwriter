<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;


use assignsubmission_pxaiwriter\app\http\interfaces\rest;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class api implements interfaces\api
{
    private base_factory $factory;
    private rest $rest;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
        $this->rest = $this->factory->http()->json();
        $this->setup_api();
    }

    public function generate_ai_text(string $user_text): string
    {
        $params = $this->get_default_params($user_text);
        $json = $this->rest->post(
            $this->get_api_url(),
            $params,
            ''
        )->get_text();

        return trim(
            $this->get_text_from_json_response($json)
        );
    }

    public function expand_ai_text(string $user_text): string
    {
        $params = $this->get_default_params(
            $this->get_expand_text_sentence($user_text)
        );

        $json = $this->rest->post(
            $this->get_api_url(),
            $params,
            ''
        )->get_text();

        return trim(
            $this->get_text_from_json_response($json)
        );
    }

    private function get_expand_text_sentence(string $user_text): string
    {
        $command = $this->factory->moodle()->get_string('expand_command');
        return "$command : $user_text";
    }

    private function setup_api(): void
    {
        $this->rest->header()->set(
            'Authorization',
            $this->factory->setting()->admin()->get_authorization()
        );
    }

    private function get_api_url(): string
    {
        return $this->factory->setting()->admin()->get_url();
    }

    private function get_default_params(string $text): array
    {
        $settings = $this->factory->setting()->admin();
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

    private function get_openai_data(string $json): array
    {
        return $this->factory->helper()->encoding()->json()->decode_as_array($json);
    }

    private function get_data_choices(array $data): array
    {
        return $data['choices'] ?? [];
    }

    private function get_first_choice(array $choices): array
    {
        return $choices[0];
    }

    private function get_choice_text(array $choice): string
    {
        return $choice['text'] ?? '';
    }

    private function get_text_from_json_response(string $json): string
    {
        $data = $this->get_openai_data($json);
        $choices = $this->get_data_choices($data);
        $first_choice = $this->get_first_choice($choices);
        return $this->get_choice_text($first_choice);
    }
}
