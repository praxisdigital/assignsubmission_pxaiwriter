<?php

namespace assignsubmission_pxaiwriter\integration\migration;


use assignsubmission_pxaiwriter\app\migration\history_data_migration;
use assignsubmission_pxaiwriter\app\test\integration_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class history_data_migration_test extends integration_testcase
{
    public function test_migrate_history_data_from_steps_data_in_submissions(): void
    {
        $user = $this->create_user();
        $course = $this->create_course();

        $this->enrol_user($user, $course);
        self::setUser($user);

        $steps_config = [];
        $steps_config[] = [
            'step' => 1,
            'description' => 'Step 1 AI description',
            'mandatory' => true,
            'type' => 'text',
            'removable' => false,
            'isreadonly' => true,
            'custom_buttons' => ['name' => 'expand'],
            'ai_element' => true,
            'ai_expand_element' => true,
            'value' => ''
        ];
        $steps_config[] = [
            'step' => 2,
            'description' => 'Step 2 user description',
            'mandatory' => true,
            'type' => 'text',
            'removable' => false,
            'isreadonly' => true,
            'custom_buttons' => ['name' => 'expand'],
            'ai_element' => true,
            'ai_expand_element' => true,
            'value' => ''
        ];

        $assign = $this->create_assignment_with_ai_writer($course);
        $submission = $this->create_submission($assign, $user);
        $data = $this->get_ai_writer_form_data($steps_config);

        $this->save_submission(
            $assign,
            $submission,
            $data
        );

        $steps_data = $this->get_legacy_steps_data($steps_config);
        $steps_data_json = json_encode($steps_data);

        $submission_instance = $this->get_submission_instance_by_submission($submission);

        // Mimic legacy data
        $submission_instance->steps_data = $steps_data_json;
        $this->db()->update_record('assignsubmission_pxaiwriter', $submission_instance);

        // Migrate legacy data
        $migration = new history_data_migration($this->factory());
        $migration->up();

        $submission_instance = $this->get_submission_instance_by_submission($submission);

        $actual_steps_data = json_decode($submission_instance->steps_data);

        self::assertObjectHasAttribute('old_steps_data', $actual_steps_data);
        self::assertObjectHasAttribute('history_ids', $actual_steps_data);
        self::assertObjectHasAttribute('latest_history_ids', $actual_steps_data);

        $steps_data_count = count($steps_data);
        self::assertCount(
            $steps_data_count,
            $actual_steps_data->old_steps_data
        );
        self::assertCount(
            $steps_data_count,
            $actual_steps_data->history_ids
        );
        self::assertCount(
            $steps_data_count,
            $actual_steps_data->latest_history_ids
        );
    }

    private function get_legacy_steps_data(array $steps_configs): array
    {
        $steps_data = [];
        foreach ($steps_configs as $config)
        {
            $step_data = $config;
            $step_data['value'] = "Step {$config['step']} text data";
            $steps_data[] = $step_data;
        }
        return $steps_data;
    }

    private function get_submission_instance_by_submission(object $submission): object
    {
        return $this->db()->get_record('assignsubmission_pxaiwriter', [
            'submission' => $submission->id,
            'assignment' => $submission->assignment
        ]);
    }
}
