<?php

namespace assignsubmission_pxaiwriter\app\test\mock\settings;


use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\settings;
use assignsubmission_pxaiwriter\app\test\mock\mocker;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class admin_settings extends mocker implements settings
{
    private settings $settings;

    public function __construct(?settings $settings = null)
    {
        $this->settings = $settings ?? base_factory::make()->setting()->admin();
    }

    public function get_openai_token(): string
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_openai_token();
    }

    public function get_url(): string
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_url();
    }

    public function get_default(): bool
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_default();
    }

    public function get_presence_penalty(): float
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_presence_penalty();
    }

    public function get_frequency_penalty(): float
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_frequency_penalty();
    }

    public function get_top_p(): float
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_top_p();
    }

    public function get_max_tokens(): int
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_max_tokens();
    }

    public function get_temperature(): float
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_temperature();
    }

    public function get_model(): string
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_model();
    }

    public function get_attempt_count(): int
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_attempt_count();
    }
}
