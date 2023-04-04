<?php

namespace assignsubmission_pxaiwriter\app\helper\encoding;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    /**
     * @var object[]
     */
    private array $instances = [];

    public function json(): interfaces\json
    {
        return $this->instances[__FUNCTION__] ??= new json();
    }
}
