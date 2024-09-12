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
        string $assistant_text,
        string $user_text,
        string $expected_ai_text,
        array $responses
    ): array
    {
        return [
            $model,
            $assistant_text,
            $user_text,
            $expected_ai_text,
            $responses
        ];
    }

    private function get_data_provider_list(string $assistant_text, string $user_text): array
    {
        $chat_completion_data = $this->get_fake_chat_completion_api_data_default();
        $expected_chat_text = $this->get_expected_text_from_responses($chat_completion_data);

        $messages = [
            'A mighty orc that loose in the end.',
            ' The cat is victorious in the end.',
            ' But the orc is not defeated yet.',
        ];

        return [
            $this->get_provider_data(
                models::GPT_3_5_TURBO,
                $assistant_text,
                $user_text,
                $expected_chat_text,
                $chat_completion_data
            ),
            $this->get_provider_data(
                models::GPT_4,
                $assistant_text,
                $user_text,
                $expected_chat_text,
                $chat_completion_data
            ),
            $this->get_provider_data(
                models::GPT_4_O,
                $assistant_text,
                $user_text,
                $expected_chat_text,
                $chat_completion_data
            ),
            $this->get_provider_data(
                models::GPT_4_O_MINI,
                $assistant_text,
                $user_text,
                $expected_chat_text,
                $chat_completion_data
            ),
            $this->get_provider_data(
                models::GPT_3_5_TURBO,
                $assistant_text,
                $user_text,
                implode('', $messages),
                $this->get_fake_chat_completion_api_data_chain($messages)
            ),
        ];
    }

    private function get_expected_text_from_responses(array $responses): string
    {
        $expected_text = '';
        foreach ($responses as $response) {
            $expected_text .= $response->choices[0]->message->content ?? '';
        }
        return $expected_text;
    }

    public function generate_ai_text_provider(): array
    {
        return $this->get_data_provider_list(
            'Respond as if you were the king of bad grammar',
            'Makeup a story about a cat that is facing a mighty orc in the final battle'
        );
    }

    /**
     * @dataProvider generate_ai_text_provider
     * @param string $model
     * @param string $assistant_text
     * @param string $user_text
     * @param string $expected_text
     * @param object[] $responses
     * @return void
     */
    public function test_generate_ai_text(
        string $model,
        string $assistant_text,
        string $user_text,
        string $expected_text,
        array $responses
    ): void
    {
        $api = new api($this->get_fake_factory(
            $model,
            $responses
        ));
        $ai_text = $api->generate_ai_text($assistant_text, $user_text);
        self::assertSame(
            $expected_text,
            $ai_text->get_text()
        );
    }

    public function expand_ai_text_provider(): array
    {
        return $this->get_data_provider_list(
            'Respond as if you were the queen of orcs',
            'A mighty orc that loose in the end');
    }

    /**
     * @dataProvider expand_ai_text_provider
     * @param string $model
     * @param string $assistant_text
     * @param string $user_text
     * @param string $expected_text
     * @param object[] $responses
     * @return void
     */
    public function test_expand_ai_text(
        string $model,
        string $assistant_text,
        string $user_text,
        string $expected_text,
        array $responses
    ): void
    {
        $api = new api($this->get_fake_factory(
            $model,
            $responses
        ));
        $ai_text = $api->expand_ai_text($assistant_text, $user_text);
        self::assertSame(
            $expected_text,
            $ai_text->get_text()
        );
    }

    private function get_fake_factory(
        string $model,
        array $responses
    ): \assignsubmission_pxaiwriter\app\interfaces\factory
    {
        $settings = new admin_settings();
        $settings->set_mock_method('get_model', $model);

        $rest = $this->createMock(rest::class);
        $rest->method('post')
            ->willReturnCallback(static function () use (&$responses) {
                $response = current($responses);
                if ($response === false) {
                    return new response(json_encode([]));
                }

                next($responses);

                return new response(json_encode($response));
            });

        $http_factory = new \assignsubmission_pxaiwriter\app\test\mock\http\factory();
        $http_factory->set_mock_method('json', $rest);

        $setting_factory = new \assignsubmission_pxaiwriter\app\test\mock\settings\factory();
        $setting_factory->set_mock_method('admin', $settings);

        $factory = new factory();
        $factory->set_mock_method('setting', $setting_factory);
        $factory->set_mock_method('http', $http_factory);

        return $factory;
    }

    private function get_fake_chat_completion_api_data(array $response = []): object
    {
        $prompt_tokens = 54;
        $completion_tokens = 22;
        $openai_response = [
            'id' => 'chatcmpl-dvwVzFSAh4DsvWjvqJlx9lgJK3Was',
            'object' => 'chat.completion',
            'created' => 1681885800,
            'model' => 'gpt-3.5-turbo-0301',
            'usage' => [
                'prompt_tokens' => $prompt_tokens,
                'completion_tokens' => $completion_tokens,
                'total_tokens' => $prompt_tokens + $completion_tokens
            ],
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0
                ]
            ]
        ];
        $data = array_merge($openai_response, $response);
        $json = json_encode($data);
        return json_decode($json, false);
    }

    private function get_fake_chat_completion_api_data_default(array $response = []): array
    {
        return [
            $this->get_fake_chat_completion_api_data($response)
        ];
    }

    private function get_fake_chat_completion_api_data_chain(array $messages = []): array
    {
        if (empty($messages)) {
            return [];
        }

        $responses = [];
        foreach ($messages as $message) {
            $responses[] = $this->get_fake_chat_completion_api_data([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => $message
                        ],
                        'finish_reason' => 'length',
                        'index' => 0
                    ]
                ]
            ]);
        }

        $last_key = array_key_last($responses);
        $responses[$last_key]->choices[0]->finish_reason = 'stop';

        return $responses;
    }
}
