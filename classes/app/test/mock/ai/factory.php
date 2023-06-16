<?php

namespace assignsubmission_pxaiwriter\app\test\mock\ai;


use assignsubmission_pxaiwriter\app\ai\openai\interfaces\factory as openai_factory;
use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\ai\attempt\interfaces\factory as attempt_factory;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\factory as history_factory;
use assignsubmission_pxaiwriter\app\ai\interfaces\factory as ai_factory_interface;
use assignsubmission_pxaiwriter\app\ai\interfaces\formatter;
use assignsubmission_pxaiwriter\app\test\mock\mocker;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory extends mocker implements ai_factory_interface
{
    private ai_factory_interface $factory;

    public function __construct(?ai_factory_interface $factory = null)
    {
        $this->factory = $factory ?? base_factory::make()->ai();
    }

    public function attempt(): attempt_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->attempt();
    }

    public function formatter(): formatter
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->formatter();
    }

    public function history(): history_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->history();
    }

    public function openai(): openai_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->openai();
    }
}
