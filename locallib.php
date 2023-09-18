<?php

use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory_interface;
use assignsubmission_pxaiwriter\app\moodle\interfaces\factory as moodle_factory;
use assignsubmission_pxaiwriter\pxaiwriter_steps_form_element;
use assignsubmission_pxaiwriter\task\delete_user_history;

define('ASSIGNSUBMISSION_FILE_MAXFILES', 10);
define('ASSIGNSUBMISSION_PXAIWRITER_FILEAREA', 'submissions_pxaiwriter');

class assign_submission_pxaiwriter extends assign_submission_plugin
{
    public function get_name()
    {
        return $this->moodle()->get_string('pluginname');
    }

    public function get_settings(MoodleQuickForm $mform)
    {
        global $CFG;

        $steps_info = $this->get_config('pxaiwritersteps');
        $is_in_used = empty($steps_info);
        $steps = $is_in_used ? $this->get_default_steps_info() : json_decode($steps_info);

        $mform->addElement('hidden', 'assignsubmission_pxaiwriter_steps', null);
        $mform->setType('assignsubmission_pxaiwriter_steps', PARAM_RAW);

        MoodleQuickForm::registerElementType(
            'pxaiwriter_steps_section',
            "$CFG->dirroot/mod/assign/submission/pxaiwriter/classes/pxaiwriter_steps_form_element.php",
            pxaiwriter_steps_form_element::class
        );
        $mform->addElement(
            'pxaiwriter_steps_section',
            'assignsubmission_pxaiwriter_steps_config',
            null,
            null,
            $steps,
            $is_in_used
        );
    }


    /**
     * Helper for saving the settings
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data)
    {
        $this->set_config('pxaiwritersteps', $data->assignsubmission_pxaiwriter_steps);
        return true;
    }

    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data)
    {
        global $CFG;

        $repo = $this->factory()->submission()->repository();

        $steps_data = $repo->get_step_data_by_assign_submission(
            $submission,
            $this->get_config(),
            $this->assignment->get_context()
        );

        $data = $repo->add_ai_writer_submission_data(
            $submission,
            $data,
            $steps_data,
            $this->get_assignment_duedate()
        );
        $steps_data_json = $repo->get_step_data_json($steps_data);

        MoodleQuickForm::registerElementType(
            'pxaiwriter_steps_section',
            "$CFG->dirroot/mod/assign/submission/pxaiwriter/classes/pxaiwriter_steps_student_form_element.php",
            'pxaiwriter_steps_student_form_element'
        );

        $mform->addElement('pxaiwriter_steps_section', 'assignsubmission_pxaiwriter_steps_config', null, null, $data);

        $mform->addElement('hidden', 'assignsubmission_pxaiwriter_student_data', $steps_data_json);
        $mform->setType('assignsubmission_pxaiwriter_student_data', PARAM_RAW);

        return true;
    }

    public function save(stdClass $submissionorgrade, stdClass $data)
    {
        $factory = $this->factory();
        $submission_factory = $factory->submission();
        $repo = $submission_factory->repository();

        $repo->save_data($submissionorgrade, $data);

        $entity = $repo->get_by_assign_submission($submissionorgrade);
        $submission_history = $repo->get_submission_history(
            $this->assignment->get_context(),
            $submissionorgrade,
            $this->get_config()
        );

        $factory->file()->pdf()->repository()->save_submission_as_pdf($submission_history);

        if (empty($entity))
        {
            $entity = $repo->create_by_submission_history($submission_history);

            $submission_factory->event()->created(
                $this->assignment,
                $submissionorgrade,
                $entity
            );

            return true;
        }

        $repo->update_by_submission_history($entity, $submission_history);

        $submission_factory->event()->updated(
            $this->assignment,
            $submissionorgrade,
            $entity
        );

        return true;
    }

    public function submission_is_empty(stdClass $data)
    {
        return false;
    }

    public function remove(stdClass $submission)
    {
        if (!isset($submission->id)) {
            return false;
        }

        $is_deleted = $this->db()->delete_records(
            'assignsubmission_pxaiwriter',
            ['submission' => $submission->id]
        );
        if ($is_deleted) {
            $this->factory()->file()->repository()->delete_files_by_submission(
                $this->assignment->get_context(),
                $submission->id
            );
        }

        $this->factory()->submission()->repository()->delete_by_submission($submission);

        delete_user_history::schedule_by_submission_id($submission->id);

        return true;
    }

    public function get_files(stdClass $submission, stdClass $user)
    {
        return $this->factory()->file()->repository()->get_submission_files_with_path(
            $this->assignment->get_context(),
            $submission
        );
    }

    public function view_summary(stdClass $submission, &$showviewlink)
    {
        if (!$this->factory()->submission()->repository()->has_id($submission->id))
        {
            return $this->moodle()->get_string('not_available');
        }

        $showviewlink = true;
        return $this->moodle()->get_string('view_submission');
    }


    public function view(stdClass $submission)
    {
        $entity = $this->factory()->submission()->repository()->get_by_assign_submission($submission);
        if ($entity === null)
        {
            return '';
        }

        $history_ids = $entity->get_latest_step_history_ids();
        if (empty($history_ids))
        {
            return '';
        }

        $history = $this->factory()->ai()->history()->repository()->get_last_by_ids($history_ids);
        if ($history === null)
        {
            return '';
        }

        $data = trim($history->get_data());
        if (empty($data))
        {
            return '';
        }

        return "<br>" . nl2br($data, false);
    }

    public function get_editor_text($name, $submissionid)
    {
        if ($name !== 'pxaiwriter') {
            return '';
        }

        $instance = $this->get_pxaiwriter_submission($submissionid);
        if (empty($instance)) {
            return '';
        }

        return $instance->steps_data;
    }


    public function is_empty(stdClass $submission)
    {
        return false;
    }

    public function get_file_areas()
    {
        return [ASSIGNSUBMISSION_PXAIWRITER_FILEAREA => $this->get_name()];
    }


    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission)
    {
        $entity = $this->factory()->submission()->repository()->get_by_assign_submission($sourcesubmission);
        if ($entity === null)
        {
            return true;
        }

        $this->factory()->submission()->repository()->copy_to(
            $entity,
            $destsubmission->id
        );
        return true;
    }

    public function delete_instance()
    {
        $assign_id = $this->assignment->get_instance()->id;

        $this->factory()->submission()->repository()->delete_by_assignment_id($assign_id);

        $this->factory()->file()->repository()->delete_files_by_context(
            $this->assignment->get_context()
        );

        delete_user_history::schedule_by_assignment_id($assign_id);

        return true;
    }

    private function factory(): base_factory_interface
    {
        return base_factory::make();
    }

    private function db(): moodle_database
    {
        return $this->moodle()->db();
    }

    private function moodle(): moodle_factory
    {
        return $this->factory()->moodle();
    }

    public function get_config_for_external()
    {
        return (array) $this->get_config();
    }

    private function get_assignment_duedate()
    {
        try {
            return $this->assignment->get_instance()->duedate ?? null;
        } catch (Exception $e) {}
        return null;
    }

    private function get_pxaiwriter_submission($submissionid)
    {
        return $this->db()->get_record('assignsubmission_pxaiwriter', ['submission' => $submissionid]);
    }

    private function get_step_info(
        ?object $instance = null,
        string $description = ''
    ): object
    {
        $instance ??= new stdClass();
        $instance->step ??= 1;
        $instance->description ??= $description;
        $instance->mandatory = true;
        $instance->type = 'text';
        $instance->removable = false;
        $instance->isreadonly = false;
        $instance->readonly = '';
        $instance->ai_element = false;
        $instance->ai_expand_element = false;
        $instance->value = '';

        return $instance;
    }

    private function get_first_step_info(): object
    {
        return $this->get_step_info((object)[
            'step' => 1,
            'description' => $this->moodle()->get_string('first_step_description'),
            'isreadonly' => true,
            'ai_element' => true,
            'ai_expand_element' => true,
        ]);
    }

    private function get_second_step_info(): object
    {
        return $this->get_step_info((object)[
            'step' => 2,
            'description' => $this->moodle()->get_string('second_step_description'),
        ]);
    }

    private function get_default_steps_info(): array
    {
        return [
            $this->get_first_step_info(),
            $this->get_second_step_info(),
        ];
    }
}
