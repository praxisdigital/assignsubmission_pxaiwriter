<?php

namespace assignsubmission_pxaiwriter\app\ai\openai\interfaces;


use assignsubmission_pxaiwriter\app\setting\interfaces\settings;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface models
{
    public const GPT_3_5_TURBO = 'gpt-3.5-turbo';
    public const TEXT_DAVINCI_3 = 'text-davinci-003';
    public const TEXT_DAVINCI_2 = 'text-davinci-002';

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
