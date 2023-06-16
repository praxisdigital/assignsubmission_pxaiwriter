<?php

namespace assignsubmission_pxaiwriter\app\submission\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function entity(array $record = []): entity;
    public function event(): event;
    public function mapper(): mapper;
    public function repository(): repository;

    public function step_config(object $config): step_config;
}
