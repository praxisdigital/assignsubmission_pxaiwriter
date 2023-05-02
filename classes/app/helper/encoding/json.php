<?php

namespace assignsubmission_pxaiwriter\app\helper\encoding;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class json implements interfaces\json
{
    public function encode($data, int $depth = 512): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR, $depth);
    }

    public function decode(string $data, int $depth = 512)
    {
        return json_decode(
            $data,
            null,
            $depth,
            JSON_THROW_ON_ERROR
        );
    }

    public function decode_as_array(string $data, int $depth = 512): array
    {
        return json_decode(
            $data,
            true,
            $depth,
            JSON_THROW_ON_ERROR
        );
    }
}
