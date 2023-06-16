<?php

namespace assignsubmission_pxaiwriter\app\file\pdf\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface converter
{
    public function convert_to_pdf_file(string $filename, string $data): string;
}
