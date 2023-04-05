<?php

namespace assignsubmission_pxaiwriter\app\ai\interfaces;


use assignsubmission_pxaiwriter\app\ai\attempt\interfaces\factory as attempt_factory;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\factory as history_factory;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\api;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function api(): api;
    public function attempt(): attempt_factory;
    public function formatter(): formatter;
    public function history(): history_factory;
}
