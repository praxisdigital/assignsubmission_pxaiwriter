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

    public function generate_ai_text(string $assistant_text, string $user_text): interfaces\response
    {
        $chat = new chat($this->get_settings());
        $chat->system_prompt('You\'re a text completion AI used to help students complete their texts.');

        if (!empty($assistant_text)) {
            $chat->system_prompt($assistant_text);
        }

        $chat->user_prompt($user_text);
        return $this->get_response_from_chat_response(
            $this->get_chat_completion($chat)
        );
    }

    public function expand_ai_text(string $assistant_text, string $user_text): interfaces\response
    {
        $chat = new chat($this->get_settings());
        $chat->system_prompt('You\'re a text completion AI used to help students complete their texts.');

        if (!empty($assistant_text)) {
            $chat->system_prompt($assistant_text);
        }

        $chat->user_prompt(
            $this->get_expand_text_sentence($user_text)
        );
        return $this->get_response_from_chat_response(
            $this->get_chat_completion($chat)
        );
    }

    private function get_response_from_chat_response(interfaces\chat_response $chat_response): interfaces\response
    {
        $responses = $chat_response->get_responses();
        return new response(
            $this->get_chat_responses_json($responses),
            $this->get_chat_complete_message($responses)
        );
    }

    /**
     * @param interfaces\chat_response[] $responses
     * @return string
     */
    private function get_chat_responses_json(array $responses): string
    {
        $data = [];
        foreach ($responses as $response) {
            $data[] = $response->get_data();
        }
        return json_encode($data);
    }

    /**
     * @param interfaces\chat_response[] $responses
     * @return string
     */
    private function get_chat_complete_message(array $responses): string
    {
        $message = '';
        foreach ($responses as $response) {
            $message .= $response->get_message();
        }
        return $message;
    }

    private function request_chat_completion(chat $chat): interfaces\chat_response
    {
        $json = $this->rest->post(
            $this->get_api_url(),
            $chat->get_request_data()
        )->get_text();
        return $this->get_chat_response_from_json($json);
    }

    private function get_chat_completion(
        chat $chat,
        ?interfaces\chat_response $response = null
    ): interfaces\chat_response
    {
        if ($response === null) {
            $response = $this->request_chat_completion($chat);
        }

        if ($response->is_finished()) {
            return $response;
        }

        $chat->assistant_prompt($response->get_message());

        $new_response = $this->request_chat_completion($chat);
        $new_response->set_previous_response($response);

        return $this->get_chat_completion(
            $chat,
            $new_response
        );
    }

    private function get_chat_response_from_json(string $json): interfaces\chat_response
    {
        return new chat_response($json);
    }

    private function get_settings(): settings
    {
        return $this->factory->setting()->admin();
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
}
