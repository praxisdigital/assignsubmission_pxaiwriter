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
            self::GPT_4 => self::API_URL_CHAT_COMPLETIONS,
            self::GPT_4_O => self::API_URL_CHAT_COMPLETIONS,
            self::GPT_4_O_MINI => self::API_URL_CHAT_COMPLETIONS,
            self::GPT_3_5_TURBO => self::API_URL_CHAT_COMPLETIONS,
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
            self::GPT_4_O => self::GPT_4_O . " (GPT-4o)",
            self::GPT_4_O_MINI => self::GPT_4_O_MINI . " (GPT-4o Mini)",
            self::GPT_3_5_TURBO => self::GPT_3_5_TURBO . " (GPT-3.5) ($recommended)",
        ];
    }

    private function throw_on_unsupported_model(string $model): void
    {
        throw unsupported_openai_model_exception::by_model_type($model);
    }
}
