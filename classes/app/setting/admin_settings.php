<?php

namespace assignsubmission_pxaiwriter\app\setting;


use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class admin_settings implements interfaces\settings
{
    private base_factory $factory;
    private ?object $config;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
        $this->config = $this->factory->moodle()->get_config_instance();
    }

    public function get_default(): bool
    {
        return $this->config->default ?? true;
    }

    public function get_presence_penalty(): float
    {
        return $this->config->presence_penalty ?? 0;
    }

    public function get_frequency_penalty(): float
    {
        return $this->config->frequency_penalty ?? 0;
    }

    public function get_top_p(): float
    {
        return $this->config->top_p ?? 1;
    }

    public function get_max_tokens(): int
    {
        return $this->config->max_tokens ?? 256;
    }

    public function get_temperature(): float
    {
        return $this->config->temperature ?? 0.7;
    }

    public function get_model(): string
    {
        return $this->config->model ?? models::GPT_3_5_TURBO;
    }

    public function get_openai_token(): string
    {
        return $this->config->openai_token ?? '';
    }

    public function get_url(): string
    {
        $model = $this->get_model();
        if (empty($model))
        {
            $model = models::GPT_3_5_TURBO;
        }
        return $this->factory->ai()->openai()->models()->get_model_url($model);
    }

    public function get_attempt_count(): int
    {
        return $this->config->attempt_count ?? 0;
    }

    public function get_system_message(): string
    {
        return $this->config->system_message ?? "I am a helpful assistant";
    }
}
