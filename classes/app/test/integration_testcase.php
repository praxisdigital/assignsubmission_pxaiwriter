<?php

namespace assignsubmission_pxaiwriter\app\test;


use advanced_testcase;
use assignsubmission_pxaiwriter\app\test\webservice\generate_ai_text_response;
use assignsubmission_pxaiwriter\external\ai\generate_ai_text;


global $CFG;
require_once $CFG->dirroot . '/mod/assign/tests/generator.php';

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */


abstract class integration_testcase extends advanced_testcase
{
    use \mod_assign_test_generator;

    protected function setUp(): void
    {
        $this->resetAfterTest();
    }

    protected function create_user(array $record = []): object
    {
        return self::getDataGenerator()->create_user($record);
    }

    protected function create_course(array $record = []): object
    {
        return self::getDataGenerator()->create_course($record);
    }

    protected function enrol_user(
        object $user,
        object $course,
        string $role = 'student',
        string $enrol_method = 'manual'
    ): void
    {
        self::getDataGenerator()->enrol_user(
            $user->id,
            $course->id,
            $role,
            $enrol_method
        );
    }

    protected function set_config(string $name, $value = null, string $component = 'assignsubmission_pxaiwriter'): void
    {
        set_config($name, $value, $component);
    }

    protected function create_assignment(object $course, array $record = []): \mod_assign_testable_assign
    {
        return $this->create_instance($course, $record);
    }

    protected function create_assignment_with_ai_writer(object $course, int $steps = 2, array $record = []): \mod_assign_testable_assign
    {
        $record['assignsubmission_pxaiwriter_enabled'] = true;
        $record['assignsubmission_pxaiwriter_steps'] = $steps;
        return $this->create_instance($course, $record);
    }

    protected function generate_ai_text(int $assignment_id, int $step, string $text): generate_ai_text_response
    {
        $response = generate_ai_text::execute(
            $assignment_id,
            $step,
            $text
        );
        return new generate_ai_text_response($response);
    }
}
