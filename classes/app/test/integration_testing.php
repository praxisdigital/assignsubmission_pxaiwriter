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

/**
 * @mixin advanced_testcase
 * @mixin mod_assign_test_generator
 */
trait integration_testing
{
    use mod_assign_test_generator;

    protected function factory(): base_factory_interface
    {
        return factory::make();
    }

    protected function db(): moodle_database
    {
        return factory::make()->moodle()->db();
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

    protected function create_assignment(object $course, array $record = []): mod_assign_testable_assign
    {
        return $this->create_instance($course, $record);
    }

    protected function create_assignment_with_ai_writer(object $course, int $steps = 2, array $record = []): mod_assign_testable_assign
    {
        $steps_data = [];
        for ($step_number = 1; $step_number <= $steps; $step_number++)
        {
            $steps_data[] = $this->get_step_data($step_number, "Step description $step_number");
        }
        return $this->create_assignment_with_ai_writer_steps($course, $steps_data, $record);
    }

    protected function create_assignment_with_ai_writer_steps(object $course, array $steps_data, array $record = []): mod_assign_testable_assign
    {
        $record['assignsubmission_pxaiwriter_enabled'] = true;
        $record['assignsubmission_pxaiwriter_steps'] = json_encode($steps_data);
        $record['assignsubmission_pxaiwriter_step_1_additional_prompt'] = '';
        return $this->create_instance($course, $record);
    }

    protected function create_grouping(object $course, array $record = []): object
    {
        $record['courseid'] = $course->id;
        return self::getDataGenerator()->create_grouping($record);
    }

    protected function create_group(object $course, array $record = []): object
    {
        $record['courseid'] = $course->id;
        return self::getDataGenerator()->create_group($record);
    }

    protected function add_grouping_group(object $grouping, object $group): void
    {
        $is_success = self::getDataGenerator()->create_grouping_group([
            'groupingid' => $grouping->id,
            'groupid' => $group->id
        ]);

        if (!$is_success)
        {
            throw new \Exception("Failed to add group#{$group->id} to grouping#{$grouping->id}");
        }
    }

    protected function add_group_member(object $group, object $user): void
    {
        $is_success = self::getDataGenerator()->create_group_member([
            'groupid' => $group->id,
            'userid' => $user->id
        ]);

        if (!$is_success)
        {
            throw new \Exception("Failed to add user({$user->username}) to group#{$group->id}");
        }
    }

    protected function get_ai_writer_plugin(assign $assign): assign_submission_pxaiwriter
    {
        return $assign->get_submission_plugin_by_type('pxaiwriter');
    }

    protected function create_submission(
        assign $assign,
        object $user
    ): object
    {
        return $assign->get_user_submission($user->id, true);
    }

    protected function create_submission_with_group(
        assign $assign,
        object $user,
        object $group
    ): object
    {
        return $assign->get_group_submission($user->id, $group->id, true);
    }

    protected function get_ai_writer_form_data(array $steps_data = [], array $data = []): object
    {
        $step_number = 1;
        $configs = [];
        foreach ($steps_data as $step)
        {
            $step_data = (array)$step;
            $step_data['step'] ??= $step_number;
            $step_data['description'] ??= "Step $step_number description";
            $step_data['value'] ??= "Step {$step_data['step']} text data";
            $step_data['mandatory'] ??= true;
            $step_data['type'] ??= 'text';
            $step_data['removable'] ??= false;
            $step_data['mandatory'] ??= true;
            $step_data['ai_element'] ??= true;
            $step_data['ai_expand_element'] ??= true;
            $configs[] = $step_data;
            ++$step_number;
        }

        $data['assignsubmission_pxaiwriter_student_data'] = json_encode($configs);
        return (object)$data;
    }

    protected function save_submission(
        assign $assign,
        object $submission,
        object $data
    ): bool
    {
        $submission_plugin = $this->get_ai_writer_plugin($assign);
        return $submission_plugin->save($submission, $data);
    }

    protected function get_step_data(int $step, ?string $description = null, array $data = []): array
    {
        $data['step'] = $step;
        $data['description'] = $description ?? "Step $step description";
        $data['mandatory'] ??= true;
        $data['type'] ??= 'text';
        $data['removable'] ??= false;
        $data['isreadonly'] ??= false;
        $data['ai_element'] ??= false;
        $data['ai_expand_element'] ??= false;
        $data['value'] ??= '';

        return $data;
    }

    protected function get_step_data_with_ai_text(
        string $text,
        ?string $description = null
    ): array
    {
        return $this->get_step_data(1, $description, [
            'isreadonly' => true,
            'ai_element' => true,
            'ai_expand_element' => true,
            'value' => $text
        ]);
    }

    protected function get_step_data_with_user_text(
        int $step,
        string $text,
        ?string $description = null
    ): array
    {
        return $this->get_step_data($step, $description, [
            'value' => $text
        ]);
    }

    protected function get_course_module_by_submission(object $submission): cm_info
    {
        $sql = 'SELECT cm.* FROM {course_modules} cm
                    JOIN {modules} m ON m.id = cm.module
                WHERE m.name = :module_name
                    AND cm.instance = :instance_id';

        $record = $this->db()->get_record_sql($sql, [
            'module_name' => 'assign',
            'instance_id' => $submission->assignment
        ]);

        $mod_info = get_fast_modinfo($record->course);
        return $mod_info->get_cm($record->id);
    }

    protected function get_context_by_submission(object $submission): context
    {
        $course_module = $this->get_course_module_by_submission($submission);
        return $course_module->context;
    }

    protected function get_submission_configs(object $submission): object
    {
        $records = $this->db()->get_recordset('assign_plugin_config', [
            'assignment' => $submission->assignment,
            'plugin' => 'pxaiwriter',
            'subtype' => 'assignsubmission'
        ]);

        $configs = [];
        foreach ($records as $record)
        {
            $configs[$record->name] = $record->value;
        }
        $records->close();

        return (object)$configs;
    }

    protected function get_assign_id(mod_assign_testable_assign $assign): int
    {
        return $assign->get_instance()->id;
    }
}
