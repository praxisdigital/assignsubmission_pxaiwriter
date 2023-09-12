<?php

namespace assignsubmission_pxaiwriter\app\assign\interfaces;


use assign;
use core\event\base;

global $CFG;
require_once $CFG->dirroot . '/mod/assign/locallib.php';

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface mapper
{
    public function map(?object $record = null): entity;

    public function add_assign_to_event(base $event, assign $assign): void;
}
