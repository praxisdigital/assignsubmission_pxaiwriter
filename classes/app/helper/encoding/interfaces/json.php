<?php

namespace assignsubmission_pxaiwriter\app\helper\encoding\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface json
{
    public function encode($data, int $depth = 512): string;

    /**
     * @template T
     * @psalm-template T
     * @param string $data
     * @param int $depth
     * @return T|mixed
     */
    public function decode(string $data, int $depth = 512);
    public function decode_as_array(string $data, int $depth = 512): array;
}
