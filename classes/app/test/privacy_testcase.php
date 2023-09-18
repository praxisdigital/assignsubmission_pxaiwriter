<?php

namespace assignsubmission_pxaiwriter\app\test;


use core_privacy\tests\provider_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once $CFG->dirroot . '/mod/assign/tests/privacy/provider_test.php';

abstract class privacy_testcase extends provider_testcase
{
    use integration_testing;

    protected function setUp(): void
    {
        $this->resetAfterTest();
    }
}
