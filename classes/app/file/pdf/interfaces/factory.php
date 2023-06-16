<?php

namespace assignsubmission_pxaiwriter\app\file\pdf\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function converter(): converter;
    public function repository(): repository;
}
