<?php

namespace assignsubmission_pxaiwriter\app\ai\openai\interfaces;


use assignsubmission_pxaiwriter\app\setting\interfaces\settings;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface models
{
    /** @var string Current API */
    public const API_URL_CHAT_COMPLETIONS = 'https://api.openai.com/v1/chat/completions';

    /** @var string Legacy API */
    public const API_URL_TEXT_COMPLETIONS = 'https://api.openai.com/v1/completions';

    public const GPT_4 = 'gpt-4';
    public const GPT_4_O = 'gpt-4o';
    public const GPT_4_O_MINI = 'gpt-4o-mini';
    public const GPT_3_5_TURBO = 'gpt-3.5-turbo';

    /**
     * @return string[]
     */
    public function get_model_urls(): array;

    public function get_model_url(string $model): string;

    public function get_api_url_by_setting(settings $settings): string;

    /**
     * @return array<string, string>
     */
    public function get_models_list(): array;
}
