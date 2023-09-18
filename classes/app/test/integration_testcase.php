<?php

namespace assignsubmission_pxaiwriter\app\test;


use advanced_testcase;
use assign;
use assign_submission_pxaiwriter;
use assignsubmission_pxaiwriter\app\factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory_interface;
use cm_info;
use context;
use mod_assign_test_generator;
use mod_assign_testable_assign;
use moodle_database;


global $CFG;
require_once $CFG->dirroot . '/mod/assign/tests/generator.php';
require_once $CFG->dirroot . '/mod/assign/submission/pxaiwriter/locallib.php';

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */


abstract class integration_testcase extends advanced_testcase
{
    use integration_testing;

    protected function setUp(): void
    {
        $this->resetAfterTest();
    }
}
