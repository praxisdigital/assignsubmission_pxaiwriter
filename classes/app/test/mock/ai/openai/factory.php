<?php

namespace assignsubmission_pxaiwriter\app\test\mock\ai\openai;


use assignsubmission_pxaiwriter\app\ai\openai\interfaces\api;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\factory as openai_factory_interface;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\mapper;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\response;
use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\test\mock\mocker;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory extends mocker implements openai_factory_interface
{
    private openai_factory_interface $factory;

    public function __construct(?openai_factory_interface $factory = null)
    {
        $this->factory = $factory ?? base_factory::make()->ai()->openai();
    }

    public function api(): api
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->api();
    }

    public function mapper(): mapper
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->mapper();
    }

    public function models(): models
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->models();
    }

    public function response(string $json, string $text): response
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__, $json, $text);
        }
        return $this->factory->response($json, $text);
    }
}
