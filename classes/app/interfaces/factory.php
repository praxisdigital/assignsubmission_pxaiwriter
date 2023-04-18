<?php

namespace assignsubmission_pxaiwriter\app\interfaces;


use assignsubmission_pxaiwriter\app\ai\interfaces\factory as ai_factory;
use assignsubmission_pxaiwriter\app\assign\interfaces\factory as assign_factory;
use assignsubmission_pxaiwriter\app\helper\interfaces\factory as helper_factory;
use assignsubmission_pxaiwriter\app\http\interfaces\factory as http_factory;
use assignsubmission_pxaiwriter\app\moodle\interfaces\factory as moodle_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\factory as setting_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public const COMPONENT = 'assignsubmission_pxaiwriter';

    public function assign(): assign_factory;

    public function ai(): ai_factory;

    public function helper(): helper_factory;

    public function http(): http_factory;

    public function moodle(): moodle_factory;

    public function setting(): setting_factory;
}
