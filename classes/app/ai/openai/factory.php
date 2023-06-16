<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private \assignsubmission_pxaiwriter\app\interfaces\factory $factory;
    private array $instances = [];

    public function __construct(\assignsubmission_pxaiwriter\app\interfaces\factory $factory)
    {
        $this->factory = $factory;
    }

    public function api(): interfaces\api
    {
        return $this->instances[__FUNCTION__] ??= new api($this->factory);
    }

    public function mapper(): interfaces\mapper
    {
        return $this->instances[__FUNCTION__] ??= new mapper($this->factory);
    }

    public function models(): interfaces\models
    {
        return $this->instances[__FUNCTION__] ??= new models();
    }

    public function response(
        string $json,
        string $text
    ): interfaces\response
    {
        return new response(
            $json,
            $text
        );
    }
}
