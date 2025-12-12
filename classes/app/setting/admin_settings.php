<?php

namespace assignsubmission_pxaiwriter\app\setting;


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

    public function get_attempt_count(): int
    {
        return $this->config->attempt_count ?? 0;
    }

    public function get_system_message(): string
    {
        return $this->config->system_message ?? "I am a helpful assistant";
    }
}
