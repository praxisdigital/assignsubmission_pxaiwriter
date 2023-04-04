<?php

namespace assignsubmission_pxaiwriter\app;


use assignsubmission_pxaiwriter\app\assign\interfaces\factory as assign_factory;
use assignsubmission_pxaiwriter\app\helper\interfaces\factory as helper_factory;
use assignsubmission_pxaiwriter\app\ai\interfaces\factory as ai_factory;
use assignsubmission_pxaiwriter\app\http\interfaces\factory as http_factory;
use assignsubmission_pxaiwriter\app\moodle\interfaces\factory as moodle_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\factory as setting_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private static ?factory $instance = null;
    private array $factories = [];

    public static function make(): interfaces\factory
    {
        return self::$instance ??= new static();
    }

    public function ai(): ai_factory
    {
        return $this->factories[__FUNCTION__] ??= new ai\factory($this);
    }

    public function assign(): assign_factory
    {
        return $this->factories[__FUNCTION__] ??= new assign\factory($this);
    }

    /**
     * @template T
     * @psalm-template T
     * @param array $items
     * @return interfaces\collection<T>
     */
    public function collection(array $items = []): interfaces\collection
    {
        return new collection($items);
    }

    public function helper(): helper_factory
    {
        return $this->factories[__FUNCTION__] ??= new helper\factory($this);
    }

    public function http(): http_factory
    {
        return $this->factories[__FUNCTION__] ??= new http\factory($this);
    }

    public function moodle(): moodle_factory
    {
        return $this->factories[__FUNCTION__] ??= new moodle\factory();
    }

    public function setting(): setting_factory
    {
        return $this->factories[__FUNCTION__] ??= new setting\factory($this);
    }
}
