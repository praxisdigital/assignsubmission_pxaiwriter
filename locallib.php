<?php

use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory_interface;

define('ASSIGNSUBMISSION_FILE_MAXFILES', 10);
define('ASSIGNSUBMISSION_PXAIWRITER_FILEAREA', 'submissions_pxaiwriter');

class assign_submission_pxaiwriter extends assign_submission_plugin
{
    private function factory(): base_factory_interface
    {
        return base_factory::make();
    }

    /**
     * Gets the current assignment id by the loaded object
     *
     * @return int
     */
    private function get_assignment_id()
    {
        try {
            $assignmentId = $this->assignment->has_instance() ? $this->assignment->get_instance()->id : null;
            return $assignmentId;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Helper function to get the due date of the currently loaded assignment
     *
     * @return void
     */
    private function get_assignment_duedate()
    {
        try {
            $duedate = $this->assignment->has_instance() ? $this->assignment->get_instance()->duedate : null;
            return $duedate;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns the availale submission for a particular submission ID
     *
     * @param [type] $submissionid
     * @return object
     */
    private function get_pxaiwriter_submission($submissionid)
    {
        global $DB;

        return $DB->get_record('assignsubmission_pxaiwriter', array('submission' => $submissionid));
    }

    /**
     * Gets the plugin name fom the locale
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('pluginname', 'assignsubmission_pxaiwriter');
    }

    /**
     * Gets the assignment specific settings of the PXAIWriter plugin
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform)
    {
        global $CFG, $DB;

        $aiwritersteps = $this->get_config('pxaiwritersteps');

        $stepList = array();

        if (!$aiwritersteps) {
            $description = get_string('first_step_description', 'assignsubmission_pxaiwriter');

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

            $description = get_string('second_step_description', 'assignsubmission_pxaiwriter');
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

        $hasUsedInAssignments = $assignmentId != null ? $DB->record_exists('assignsubmission_pxaiwriter', ['assignment' => $assignmentId]) : false;

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

    /**
     * Add form elements for settings
     *
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */
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

    private function get_pdf_html(
        string $step_title,
        string $description,
        string $text
    ): string {
        $html = '<h4 style="margin: 10px 0px 10px 0px;"><b>Step ' . $step_title .  "</b></h4>";
        $html .= '<div style="color:#808080;margin: 0px 0px 10px 0px;"><span><i>' . $description .  "</i></span></div>";
        $html .= '<hr><div style="margin: 0px 0px 10px 0px;"></div>';
        $html .= $text;
        return $html;
    }

    private function get_diff(
        string $step_title,
        string $description,
        string $granularity,
        string $previous_text,
        string $current_text
    ): string
    {
        return $this->get_pdf_html(
            $step_title,
            $description,
            $this->getDiffRenderedHtml($previous_text, $current_text, $granularity)
        );
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

    /**
     * Get a unique file name for the pdf for given submission.
     *
     * @param int $submissionid The submission Id
     * @param int $userid The student's user Id
     */
    private function get_pdf_file_name(int $assignmentid, int $userid)
    {
        return $assignmentid . "_" . $userid . "_" . strtotime("now") . ".pdf";
    }

    private function get_edit_options()
    {
        $editoroptions = array(
            'noclean' => false,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $this->assignment->get_course()->maxbytes,
            'context' => $this->assignment->get_context(),
            'return_types' => (FILE_INTERNAL | FILE_EXTERNAL | FILE_CONTROLLED_LINK),
            'removeorphaneddrafts' => true // Whether or not to remove any draft files which aren't referenced in the text.
        );
        return $editoroptions;
    }

    /**
     * Delete pdf files from the store for given submission.
     *
     * @param int $submissionid The submission Id
     */
    private function delete_pdf_file($submissionid)
    {
        $fs = get_file_storage();
        $existingfiles = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submissionid
        );

        foreach ($existingfiles as $ef) {
            if ($ef) {
                $ef->delete();
            }
        }
    }

    public function submission_is_empty(stdClass $data)
    {
        return false;
    }


    /**
     * Remove a submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     * @throws dml_exception
     */
    public function remove(stdClass $submission)
    {
        global $DB;
        if (!isset($submission->id)) {
            return false;
        }

        $is_deleted = $DB->delete_records('assignsubmission_pxaiwriter', array('submission' => $submission->id));
        if ($is_deleted) {
            $this->delete_pdf_file($submission->id);
        }

        $this->factory()->submission()->repository()->delete_by_submission($submission);

        return true;
    }


    public function get_files(stdClass $submission, stdClass $user)
    {
        $result = array();
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
            if (isset($submission->exportfullpath) && $submission->exportfullpath == false) {
                $result[$file->get_filename()] = $file;
            } else {
                $result[$file->get_filepath() . $file->get_filename()] = $file;
            }
        }
        return $result;
    }

    public function view_summary(stdClass $submission, &$showviewlink)
    {
        $subm = $this->get_pxaiwriter_submission($submission->id);
        if ($subm) {
            $showviewlink = true;
            return  get_string('view_submission', 'assignsubmission_pxaiwriter');
        } else {
            return  get_string('not_available', 'assignsubmission_pxaiwriter');
        }
    }

    /**
     * Returns the last step content of a submission 
     *
     * @param stdClass $submission
     * @return string
     */
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

    /**
     * Helper function for backup restore to get the content of a submission
     *
     * @param [type] $name
     * @param [type] $submissionid
     * @return void
     */
    public function get_editor_text($name, $submissionid)
    {
        if ($name == 'pxaiwriter') {
            $pxaiwritersubmission = $this->get_pxaiwriter_submission($submissionid);
            if ($pxaiwritersubmission) {
                return $pxaiwritersubmission->steps_data;
            }
        }

        return '';
    }


    public function is_empty(stdClass $submission)
    {
        return false;
    }

    public function get_file_areas()
    {
        return array(ASSIGNSUBMISSION_PXAIWRITER_FILEAREA => $this->get_name());
    }

    /**
     * Copy the assignsubmission_pxaiwriter record.   
     *
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     * @return void
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission)
    {
        global $DB;
        if ($pxaisubmission = $this->get_pxaiwriter_submission($sourcesubmission->id)) {
            unset($pxaisubmission->id);
            $pxaisubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_pxaiwriter', $pxaisubmission);
        }
        return true;
    }

    /**
     * format the info for each submission plugin add_to_log   
     *
     * @param stdClass $submission
     * @return void
     */
    public function format_for_log(stdClass $submission)
    {

        $fileloginfo = '';
        $fileloginfo .= 'PXAIWriter submission.<br>';

        return $fileloginfo;
    }

    /**
     * Delete instance
     *
     * @return void
     */
    public function delete_instance()
    {
        global $DB;
        // will throw exception on failure                                                                                          
        $DB->delete_records('assignsubmission_pxaiwriter', array('assignment' => $this->assignment->get_instance()->id));

        $fs = get_file_storage();

        $existingfiles = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA
        );

        foreach ($existingfiles as $ef) {
            if ($ef) {
                $ef->delete();
            }
        }

        return true;
    }

    /**
     * Summary : Creates the comparrison view of two string in HTML
     *
     * @param [type] $textOne
     * @param [type] $textTwo
     * @param [type] $granularity
     * @param string $delReplaceTag
     * @return string
     */
    public function getDiffRenderedHtml($textOne, $textTwo, $granularity = "word", $delReplaceTag = '<span style="color:red;background-color:#ffdddd;text-decoration:line-through;">', $insReplaceTag = '<span style="color:green;background-color:#ddffdd;text-decoration:none;">')
    {
        global $CFG;
        require_once("$CFG->dirroot/mod/assign/submission/pxaiwriter/vendor/autoload.php");

        switch ($granularity) {
            case "word":
                $grOption = new FineDiff\Granularity\Word();
                break;
            case "sentence":
                $grOption = new FineDiff\Granularity\Sentence();
                break;
            case "paragraph":
                $grOption = new FineDiff\Granularity\Paragraph();
                break;
            default:
                $grOption = new FineDiff\Granularity\Character();
                break;
        }

        $diff = new FineDiff\Diff();
        $diff->setGranularity($grOption);

        $optionCode =  $diff->getOperationCodes($textOne, $textTwo);
        $renderer = new FineDiff\Render\Html();
        $result = $renderer->process($textOne, $optionCode);
        $result = str_replace("\n", "<br>", $result);

        if ($delReplaceTag) {
            $result = str_replace("<del>", $delReplaceTag, $result);
            $result = str_replace("</del>", "</span>", $result);
        }

        if ($insReplaceTag) {
            $result = str_replace("<ins>", $insReplaceTag, $result);
            $result = str_replace("</ins>", "</span>", $result);
        }

        return $result;
    }

    /**
     * Summary : Gets the pxaiwriter admin settings from config_plugins table
     *              PLEASE RE-USE THIS FUNCTION!!!
     * Created By : Nilaksha
     * Created At : 05/01/2023
     *
     * @param [type] $setting
     * @return object
     */
    function getPluginAdminSettings($setting = "", $pluginName = 'assignsubmission_pxaiwriter')
    {

        // last_modified_by
        // api_key
        // presence_penalty
        // frequency_penalty
        // top_p
        // max_tokens
        // temperature
        // model
        // authorization
        // content_type
        // url
        // default
        // installrunning
        // version
        // granularity

        global $DB;
        if ($setting) {
            $dbparams = array(
                'plugin' => $pluginName,
                'name' => $setting
            );
            $result = $DB->get_record('config_plugins', $dbparams, '*', IGNORE_MISSING);

            if ($result) {
                return $result->value;
            }

            return false;
        }

        $dbparams = array(
            'plugin' => $pluginName,
        );
        $results = $DB->get_records('config_plugins', $dbparams);

        $config = new stdClass();
        if (is_array($results)) {
            foreach ($results as $setting) {
                $name = $setting->name;
                $config->$name = $setting->value;
            }
        }
        return $config;
    }

    public function get_config_for_external()
    {
        return (array) $this->get_config();
    }
}
