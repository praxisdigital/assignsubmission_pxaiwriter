<?php

namespace assignsubmission_pxaiwriter\integration\submission;


use assignsubmission_pxaiwriter\app\submission\repository;
use assignsubmission_pxaiwriter\app\test\integration_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class repository_test extends integration_testcase
{
    public function test_get_step_data_by_assign_submission_in_an_empty_submission(): void
    {
        $user = $this->create_user();
        $course = $this->create_course();
        $ai_writer = $this->create_assignment_with_ai_writer($course);

        $this->enrol_user($user, $course);

        $submission = $this->create_submission($ai_writer, $user);
        $configs = $this->get_submission_configs($submission);


        $repo = new repository(
            $this->factory()
        );

        $steps_data = $repo->get_step_data_by_assign_submission($submission, $configs, $ai_writer->get_context());

        foreach ($steps_data as $step)
        {
            self::assertEmpty($step->value);
        }
    }
}
