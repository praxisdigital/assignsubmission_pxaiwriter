<?php

namespace assignsubmission_pxaiwriter\app\test\mock\http;


use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\http\interfaces\factory as http_factory_interface;
use assignsubmission_pxaiwriter\app\http\interfaces\header;
use assignsubmission_pxaiwriter\app\http\interfaces\rest;
use assignsubmission_pxaiwriter\app\test\mock\mocker;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory extends mocker implements http_factory_interface
{
    private http_factory_interface $factory;

    public function __construct(?http_factory_interface $factory = null)
    {
        $this->factory = $factory ?? base_factory::make()->http();
    }

    public function json(?header $header = null): rest
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__, $header);
        }
        return $this->factory->json($header);
    }
}
