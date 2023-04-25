<?php

namespace assignsubmission_pxaiwriter\app\test\mock;


use assignsubmission_pxaiwriter\app\ai\interfaces\factory as ai_factory;
use assignsubmission_pxaiwriter\app\assign\interfaces\factory as assign_factory;
use assignsubmission_pxaiwriter\app\helper\interfaces\factory as helper_factory;
use assignsubmission_pxaiwriter\app\http\interfaces\factory as http_factory;
use assignsubmission_pxaiwriter\app\interfaces\collection;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory_interface;
use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\moodle\interfaces\factory as moodle_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\factory as setting_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory extends mocker implements base_factory_interface
{
    private base_factory_interface $factory;

    public function __construct(?base_factory_interface $factory = null)
    {
        $this->factory = $factory ?? base_factory::make();
    }

    public function assign(): assign_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->assign();
    }

    public function ai(): ai_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->ai();
    }

    public function collection(array $items = []): collection
    {
        return $this->call_mock_method(__FUNCTION__, $items) ?? $this->factory->collection($items);
    }

    public function helper(): helper_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->helper();
    }

    public function http(): http_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->http();
    }

    public function moodle(): moodle_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->moodle();
    }

    public function setting(): setting_factory
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->setting();
    }
}
