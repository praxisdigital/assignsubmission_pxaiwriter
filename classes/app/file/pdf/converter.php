<?php

namespace assignsubmission_pxaiwriter\app\file\pdf;


use pdf;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once $CFG->libdir . '/pdflib.php';

class converter implements interfaces\converter
{
    private pdf $pdf;

    public function __construct(pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    public function convert_to_pdf_file(string $filename, string $data): string
    {
        $this->pdf->AddPage();
        $this->pdf->writeHTML($data, false, false, true);
        $this->pdf->lastPage();
        return $this->pdf->Output($filename, 'S');
    }
}
