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
    public function get_attempt_count(): int
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_attempt_count();
    }

    public function get_system_message(): string
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->settings->get_system_message();
    }
}
