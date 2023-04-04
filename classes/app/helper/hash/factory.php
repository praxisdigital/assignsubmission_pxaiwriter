<?php

namespace assignsubmission_pxaiwriter\app\helper\hash;


//use assignsubmission_pxaiwriter\app\helper\hash\interfaces\hash_function;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private array $instances = [];

    public function sha256(): interfaces\hash_function
    {
        return $this->instances['sha256'] ??= new hash_function('sha256');
    }
}
