<?php

namespace assignsubmission_pxaiwriter\app\helper\hash;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class hash_function implements interfaces\hash_function
{
    private string $algorithm;

    public function __construct(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function digest(string $data): string
    {
        return hash($this->algorithm, $data);
    }
}
