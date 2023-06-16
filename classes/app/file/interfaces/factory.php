<?php

namespace assignsubmission_pxaiwriter\app\file\interfaces;


use assignsubmission_pxaiwriter\app\file\pdf\interfaces\factory as pdf_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function pdf(): pdf_factory;
    public function repository(): repository;
}
