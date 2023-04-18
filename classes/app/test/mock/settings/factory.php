<?php

namespace assignsubmission_pxaiwriter\app\test\mock\settings;


use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\factory as setting_factory_interface;
use assignsubmission_pxaiwriter\app\setting\interfaces\settings;
use assignsubmission_pxaiwriter\app\test\mock\mocker;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory extends mocker implements setting_factory_interface
{
    private setting_factory_interface $factory;

    public function __construct(?setting_factory_interface $factory = null)
    {
        $this->factory = $factory ?? base_factory::make()->setting();
    }

    public function admin(): settings
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->admin();
    }
}
