<?php

namespace assignsubmission_pxaiwriter\app\file\pdf;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use pdf;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once $CFG->libdir . '/pdflib.php';

class factory implements interfaces\factory
{
    private base_factory $factory;
    private array $instances = [];

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function converter(): interfaces\converter
    {
        return new converter($this->get_pdf());
    }

    public function repository(): interfaces\repository
    {
        return $this->instances[__FUNCTION__] ??= new repository($this->factory);
    }

    private function get_pdf(): pdf
    {
        return new pdf();
    }
}
