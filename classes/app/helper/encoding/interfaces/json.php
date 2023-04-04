<?php

namespace assignsubmission_pxaiwriter\app\helper\encoding\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface json
{
    public function encode($data, int $depth = 512): string;
    public function decode(string $data, int $depth = 512): object;
    public function decode_as_array(string $data, int $depth = 512): array;
}
