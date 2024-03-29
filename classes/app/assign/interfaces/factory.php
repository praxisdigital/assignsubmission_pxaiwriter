<?php

namespace assignsubmission_pxaiwriter\app\assign\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function entity(array $record = []): entity;
    public function mapper(): mapper;
    public function repository(): repository;
}
