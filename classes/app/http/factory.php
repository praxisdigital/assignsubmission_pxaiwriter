<?php

namespace assignsubmission_pxaiwriter\app\http;


use assignsubmission_pxaiwriter\app\http\interfaces\header;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

global $CFG;
require_once $CFG->libdir . '/filelib.php';

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function json(?header $header = null): interfaces\rest
    {
        return new json_rest($this->factory, $header);
    }
}
