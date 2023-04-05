<?php

namespace assignsubmission_pxaiwriter\app\helper\interfaces;


use assignsubmission_pxaiwriter\app\helper\hash\interfaces\factory as hash_factory;
use assignsubmission_pxaiwriter\app\helper\encoding\interfaces\factory as encoding_factory;
use assignsubmission_pxaiwriter\app\helper\times\interfaces\factory as times_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function hash(): hash_factory;
    public function encoding(): encoding_factory;
    public function times(): times_factory;
}
