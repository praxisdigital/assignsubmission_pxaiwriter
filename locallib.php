<?php

use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory_interface;
use assignsubmission_pxaiwriter\app\moodle\interfaces\factory as moodle_factory;
use assignsubmission_pxaiwriter\task\delete_user_history;

define('ASSIGNSUBMISSION_FILE_MAXFILES', 10);
define('ASSIGNSUBMISSION_PXAIWRITER_FILEAREA', 'submissions_pxaiwriter');

class assign_submission_pxaiwriter extends assign_submission_plugin
{
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

    private function get_assignment_id()
    {
        try {
            return $this->assignment->get_instance()->id ?? null;
        } catch (Exception $e) {}
        return null;
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


    public function get_name()
    {
        return $this->moodle()->get_string('pluginname');
    }

    public function get_settings(MoodleQuickForm $mform)
    {
        global $CFG;

        $aiwritersteps = $this->get_config('pxaiwritersteps');

        $stepList = [];

        if (!$aiwritersteps) {
            $description = $this->moodle()->get_string('first_step_description');

            $step1 = new stdClass();
            $step1->step = 1;
            $step1->description = $description;
            $step1->mandatory = true;
            $step1->type = 'text';
            $step1->removable = false;
            $step1->isreadonly = true;
            $step1->readonly = '';
            $step1->ai_element = true;
            $step1->ai_expand_element = true;
            $step1->value = '';

            $description = $this->moodle()->get_string('second_step_description');
            $step2 = new stdClass();
            $step2->step = 2;
            $step2->description = $description;
            $step2->mandatory = true;
            $step2->type = 'text';
            $step2->removable = false;
            $step2->isreadonly = false;
            $step2->readonly = '';
            $step2->ai_element = false;
            $step2->ai_expand_element = false;
            $step2->value = '';

            array_push($stepList, $step1, $step2);
        } else {
            $stepList = json_decode($aiwritersteps);
        }

        $assignmentId = $this->get_assignment_id();

        $hasUsedInAssignments = $assignmentId != null && $this->db()->record_exists(
                'assignsubmission_pxaiwriter',
                ['assignment' => $assignmentId]
            );

        $mform->addElement('hidden', 'assignsubmission_pxaiwriter_steps', null);
        $mform->setType('assignsubmission_pxaiwriter_steps', PARAM_RAW);

        MoodleQuickForm::registerElementType(
            'pxaiwriter_steps_section',
            "$CFG->dirroot/mod/assign/submission/pxaiwriter/classes/pxaiwriter_steps_form_element.php",
            'pxaiwriter_steps_form_element'
        );
        $mform->addElement('pxaiwriter_steps_section', 'assignsubmission_pxaiwriter_steps_config', null, null, $stepList, $hasUsedInAssignments);
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

    private function delete_pdf_file($submissionid)
    {
        $storage = get_file_storage();
        $files = $storage->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submissionid
        );

        foreach ($files as $file) {
            $file->delete();
        }
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
            $this->delete_pdf_file($submission->id);
        }

        $this->factory()->submission()->repository()->delete_by_submission($submission);

        delete_user_history::schedule_by_submission_id($submission->id);

        return true;
    }


    public function get_files(stdClass $submission, stdClass $user)
    {
        $result = [];
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id,
            'timemodified',
            false
        );

        foreach ($files as $file) {
            // Do we return the full folder path or just the file name?
            if (isset($submission->exportfullpath) && !$submission->exportfullpath) {
                $result[$file->get_filename()] = $file;
                continue;
            }
            $result[$file->get_filepath() . $file->get_filename()] = $file;
        }
        return $result;
    }

    public function view_summary(stdClass $submission, &$showviewlink)
    {
        $instance = $this->get_pxaiwriter_submission($submission->id);
        if (empty($instance)) {
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
        $instance = $this->get_pxaiwriter_submission($sourcesubmission->id);
        if (!empty($instance)) {
            unset($instance->id);
            $instance->submission = $destsubmission->id;
            $this->db()->insert_record('assignsubmission_pxaiwriter', $instance);
        }
        return true;
    }

    public function delete_instance()
    {
        $assign_id = $this->assignment->get_instance()->id;

        // will throw exception on failure
        $this->db()->delete_records('assignsubmission_pxaiwriter', [
            'assignment' => $assign_id
        ]);

        $storage = get_file_storage();

        $files = $storage->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA
        );

        foreach ($files as $file) {
            $file->delete();
        }

        delete_user_history::schedule_by_assignment_id($assign_id);

        return true;
    }

    public function get_config_for_external()
    {
        return (array) $this->get_config();
    }
}
