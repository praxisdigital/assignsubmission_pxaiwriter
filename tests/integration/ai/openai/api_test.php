<?php

namespace assignsubmission_pxaiwriter\integration\ai\openai;


use assignsubmission_pxaiwriter\app\ai\openai\api;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models;
use assignsubmission_pxaiwriter\app\http\interfaces\rest;
use assignsubmission_pxaiwriter\app\http\response;
use assignsubmission_pxaiwriter\app\test\integration_testcase;
use assignsubmission_pxaiwriter\app\test\mock\factory;
use assignsubmission_pxaiwriter\app\test\mock\settings\admin_settings;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class api_test extends integration_testcase
{
    private function get_provider_data(
        string $model,
        string $user_text,
        string $expected_ai_text,
        array $response_data
    ): array
    {
        return [
            $model,
            $user_text,
            $expected_ai_text,
            $response_data
        ];
    }

    private function get_data_provider_list(string $user_text): array
    {
        $chat_completion_data = $this->get_fake_chat_completion_api_data();
        $expected_chat_text = $chat_completion_data['choices'][0]['message']['content'];

        $text_completion_data = $this->get_fake_text_completion_api_data();
        $expected_text = $text_completion_data['choices'][0]['text'];

        return [
            $this->get_provider_data(
                models::GPT_3_5_TURBO,
                $user_text,
                $expected_chat_text,
                $chat_completion_data
            ),
            $this->get_provider_data(
                models::TEXT_DAVINCI_2,
                $user_text,
                $expected_text,
                $text_completion_data
            ),
            $this->get_provider_data(
                models::TEXT_DAVINCI_3,
                $user_text,
                $expected_text,
                $text_completion_data
            ),
        ];
    }

    public function generate_ai_text_provider(): array
    {
        return $this->get_data_provider_list(
            'Makeup a story about a cat that is facing a mighty orc in the final battle'
        );
    }

    /**
     * @dataProvider generate_ai_text_provider
     * @param string $model
     * @param string $user_text
     * @param string $expected_text
     * @param array $response_data
     * @return void
     */
    public function test_generate_ai_text(
        string $model,
        string $user_text,
        string $expected_text,
        array $response_data
    ): void
    {
        $api = new api($this->get_fake_factory(
            $model,
            $response_data
        ));
        $ai_text = $api->generate_ai_text($user_text);
        self::assertSame(
            $expected_text,
            $ai_text->get_text()
        );
    }

    public function expand_ai_text_provider(): array
    {
        return $this->get_data_provider_list('A mighty orc that loose in the end');
    }

    /**
     * @dataProvider expand_ai_text_provider
     * @param string $model
     * @param string $user_text
     * @param string $expected_text
     * @param array $response_data
     * @return void
     */
    public function test_expand_ai_text(
        string $model,
        string $user_text,
        string $expected_text,
        array $response_data
    ): void
    {
        $api = new api($this->get_fake_factory(
            $model,
            $response_data
        ));
        $ai_text = $api->expand_ai_text($user_text);
        self::assertSame(
            $expected_text,
            $ai_text->get_text()
        );
    }

    private function get_fake_factory(
        string $model,
        array $response_data
    ): \assignsubmission_pxaiwriter\app\interfaces\factory
    {
        $settings = new admin_settings();
        $settings->set_mock_method('get_model', $model);

        $rest = $this->createMock(rest::class);
        $rest->method('post')
            ->willReturn(new response(json_encode($response_data)));

        $http_factory = new \assignsubmission_pxaiwriter\app\test\mock\http\factory();
        $http_factory->set_mock_method('json', $rest);

        $setting_factory = new \assignsubmission_pxaiwriter\app\test\mock\settings\factory();
        $setting_factory->set_mock_method('admin', $settings);

        $factory = new factory();
        $factory->set_mock_method('setting', $setting_factory);
        $factory->set_mock_method('http', $http_factory);

        return $factory;
    }

    private function get_fake_text_completion_api_data(): array
    {
        return [
            'id' => "cmpl-divIuq2NRTHNUFcAwMZK3FbHaTQdS",
            'object' => "text_completion",
            'created' => 1681885800,
            'model' => "text-davinci-003",
            'choices' => [
                [
                    "text" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    "index" => 0,
                    "logprobs" => null,
                    "finish_reason" => "stop"
                ]
            ],
            'usage' => [
                'prompt_tokens' => 11,
                'completion_tokens' => 222,
                'total_tokens' => 233
            ]
        ];
    }

    private function get_fake_chat_completion_api_data(): array
    {
        return [
            'id' => 'chatcmpl-dvwVzFSAh4DsvWjvqJlx9lgJK3Was',
            'object' => 'chat.completion',
            'created' => 1681885800,
            'model' => 'gpt-3.5-turbo-0301',
            'usage' => [
                'prompt_tokens' => 54,
                'completion_tokens' => 256,
                'total_tokens' => 310
            ],
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
                    ],
                    'finish_reason' => 'length',
                    'index' => 0
                ]
            ]
        ];
    }
}
