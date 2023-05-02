<?php

namespace assignsubmission_pxaiwriter\app\submission\interfaces;


use assign;
use assignsubmission_pxaiwriter\app\submission\interfaces\entity as submission_entity;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once $CFG->dirroot . '/mod/assign/locallib.php';

interface event
{
    public function created(
        assign $assign,
        object $submission,
        submission_entity $submission_entity
    ): void;

    public function updated(
        assign $assign,
        object $submission,
        submission_entity $submission_entity
    ): void;
}
