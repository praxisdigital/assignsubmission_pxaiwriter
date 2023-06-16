<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;


use assignsubmission_pxaiwriter\app\http\interfaces\rest;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\settings;

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

    public function generate_ai_text(string $user_text): interfaces\response
    {
        $params = $this->get_default_params($user_text);
        $json = $this->rest->post(
            $this->get_api_url(),
            $params
        )->get_text();

        return $this->get_response_from_json($json);
    }

    public function expand_ai_text(string $user_text): interfaces\response
    {
        $params = $this->get_default_params(
            $this->get_expand_text_sentence($user_text)
        );

        $json = $this->rest->post(
            $this->get_api_url(),
            $params
        )->get_text();

        return $this->get_response_from_json($json);
    }

    private function get_response_from_json(string $json): interfaces\response
    {
        $text = trim(
            $this->get_text_from_json_response($json)
        );
        return new response(
            $json,
            $text
        );
    }

    private function get_mapper(): interfaces\mapper
    {
        return $this->factory->ai()->openai()->mapper();
    }

    private function get_settings(): settings
    {
        return $this->factory->setting()->admin();
    }

    private function get_model(): string
    {
        return $this->get_settings()->get_model();
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
            "Bearer {$this->get_settings()->get_openai_token()}"
        );
    }

    private function get_api_url(): string
    {
        return $this->factory->ai()->openai()->models()->get_api_url_by_setting(
            $this->get_settings()
        );
    }

    private function get_default_params(string $text): array
    {
        $settings = $this->get_settings();
        return $this->get_mapper()->map_request($text, $settings);
    }

    private function get_text_from_json_response(string $json): string
    {
        return $this->get_mapper()->map_response(
            $json,
            $this->get_model()
        );
    }
}
