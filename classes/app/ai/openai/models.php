<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;


use assignsubmission_pxaiwriter\app\exceptions\unsupported_openai_model_exception;
use assignsubmission_pxaiwriter\app\factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\settings;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class models implements interfaces\models
{
    private static ?models $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function get_model_urls(): array
    {
        return [
            self::GPT_3_5_TURBO => 'https://api.openai.com/v1/chat/completions',
            self::TEXT_DAVINCI_3 => 'https://api.openai.com/v1/completions',
            self::TEXT_DAVINCI_2 => 'https://api.openai.com/v1/completions',
        ];
    }

    public function get_model_url(string $model): string
    {
        return $this->get_model_urls()[$model] ?? $this->throw_on_unsupported_model($model);
    }

    public function get_api_url_by_setting(settings $settings): string
    {
        return $this->get_model_url($settings->get_model());
    }

    public function get_models_list(): array
    {
        $recommended = factory::make()->moodle()->get_string(
            'recommended',
            null,
            'moodle'
        );

        return [
            self::GPT_3_5_TURBO => self::GPT_3_5_TURBO . " (GPT-3.5) ($recommended)",
            self::TEXT_DAVINCI_3 => self::TEXT_DAVINCI_3 . ' (GPT-3.5)',
            self::TEXT_DAVINCI_2 => self::TEXT_DAVINCI_2 . ' (GPT-3.5)',
        ];
    }

    private function throw_on_unsupported_model(string $model): void
    {
        throw unsupported_openai_model_exception::by_model_type($model);
    }
}
