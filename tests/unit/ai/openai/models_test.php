<?php

namespace assignsubmission_pxaiwriter\unit\ai\openai;


use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models as models_interface;
use assignsubmission_pxaiwriter\app\ai\openai\models;
use assignsubmission_pxaiwriter\app\setting\interfaces\settings;
use assignsubmission_pxaiwriter\app\test\unit_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class models_test extends unit_testcase
{
    public function get_ai_models_provider(): array
    {
        return [
            [models_interface::GPT_3_5_TURBO, 'https://api.openai.com/v1/chat/completions'],
            [models_interface::GPT_4_O, 'https://api.openai.com/v1/chat/completions'],
            [models_interface::GPT_4_O_MINI, 'https://api.openai.com/v1/chat/completions'],
            [models_interface::GPT_4, 'https://api.openai.com/v1/chat/completions'],
        ];
    }

    /**
     * @dataProvider get_ai_models_provider
     * @param string $model
     * @param string $expected_url
     * @return void
     */
    public function test_get_openai_chat_completion_api_url_by_setting(string $model, string $expected_url): void
    {
        $settings = $this->createMock(settings::class);
        $settings->method('get_model')
            ->willReturn($model);

        $models = new models();
        $url = $models->get_api_url_by_setting($settings);

        self::assertSame(
            $expected_url,
            $url,
            "AI model: $model is expected $expected_url. But got $url instead"
        );
    }
}
