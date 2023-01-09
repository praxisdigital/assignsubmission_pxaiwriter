<?php

define('ASSIGNSUBMISSION_FILE_MAXFILES', 10);
define('ASSIGNSUBMISSION_PXAIWRITER_FILEAREA', 'submissions_pxaiwriter');

class assign_submission_pxaiwriter extends assign_submission_plugin
{

    private function get_assignment_id()
    {
        try {
            $assignmentId = $this->assignment->has_instance() ? $this->assignment->get_instance()->id : null;
            return $assignmentId;
        } catch (Exception $e) {
            return null;
        }
    }

    private function get_assignment_duedate()
    {
        try {
            $duedate = $this->assignment->has_instance() ? $this->assignment->get_instance()->duedate : null;
            return $duedate;
        } catch (Exception $e) {
            return null;
        }
    }

    private function get_pxaiwriter_submission($submissionid)
    {
        global $DB;

        return $DB->get_record('assignsubmission_pxaiwriter', array('submission' => $submissionid));
    }

    public function get_name()
    {
        return get_string('pluginname', 'assignsubmission_pxaiwriter');
    }

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
            $step1->readonly = 'readonly';
            $step1->custom_buttons = ['do_ai_magic', 'expand'];
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
            $step2->custom_buttons = [];
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
        global $CFG, $DB, $USER;

        $pxaiwritersubmission = $this->get_pxaiwriter_submission($submission->id);
        $data->assignmentid = $this->get_assignment_id();

        $duedate = $this->get_assignment_duedate();
        $data->is_due_submission = $duedate < time();
        
        $maxaiattempts = self::getPluginAdminSettings('attempt_count') ?? 0;
        $aiattempthistoryfortoday = $DB->get_record('pxaiwriter_api_attempts', array('assignment' => $data->assignmentid, 'user' => $USER->id, 'api_attempt_date' => strtotime("today")));
        $data->exceeds_max_attempts = $aiattempthistoryfortoday ? $aiattempthistoryfortoday->api_attempts >= $maxaiattempts : false;
        $data->enabled_ai_actions = !$data->exceeds_max_attempts && !$data->is_due_submission;

        // $data = file_prepare_standard_filemanager(
        //     $data,
        //     'setps_data_file',
        //     $editoroptions,
        //     $this->assignment->get_context(),
        //     'assignsubmission_pxaiwriter',
        //     ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
        //     $submissionid
        // );

        if ($pxaiwritersubmission) {
            $data->steps_data =  json_decode($pxaiwritersubmission->steps_data);
        } else {
            $data->steps_data = json_decode($this->get_config('pxaiwritersteps'));
        }

        MoodleQuickForm::registerElementType(
            'pxaiwriter_steps_section',
            "$CFG->dirroot/mod/assign/submission/pxaiwriter/classes/pxaiwriter_steps_student_form_element.php",
            'pxaiwriter_steps_student_form_element'
        );

        $mform->addElement('pxaiwriter_steps_section', 'assignsubmission_pxaiwriter_steps_config', null, null, $data);

        $mform->addElement('hidden', 'assignsubmission_pxaiwriter_student_data', null);
        $mform->setType('assignsubmission_pxaiwriter_student_data', PARAM_RAW);

        return true;
    }

    public function save(stdClass $submission, stdClass $data)
    {
        global $USER, $DB, $CFG;

        $pxaiwritersubmission = $this->get_pxaiwriter_submission($submission->id);

        // delete existing file when updating a submission
        if ($pxaiwritersubmission) {
            $this->delete_pdf_file($submission->id);
        }

        $assignmentid = $this->get_assignment_id();
        $filename = $this->get_pdf_file_name($assignmentid, $USER->id);

        $stepsdatastring = $data->assignsubmission_pxaiwriter_student_data;
        $stepsdata = json_decode($stepsdatastring);
        $stepsdatacount = count($stepsdata);
        $initvalue = $stepsdata[0]->value;
        $finalvalue = $stepsdata[$stepsdatacount - 1]->value;

        $granularity = self::getPluginAdminSettings('granularity');
        $diffhtmlcontent = $this->getDiffRenderedHtml($initvalue, $finalvalue, $granularity);

        require_once($CFG->libdir . '/tcpdf/tcpdf.php');

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->writeHTML($diffhtmlcontent, false, false, true, false, '');
        $pdf->lastPage();

        $file = $pdf->Output($filename, 'S');

        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id,
            'id',
            false
        );


        // Prepare file record object
        $fileinfo = array(
            'contextid' => $this->assignment->get_context()->id, // ID of context
            'component' => 'assignsubmission_pxaiwriter',     // usually = table name
            'filearea' => ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,     // usually = table name
            'itemid' => $submission->id,               // usually = ID of row in table
            'filepath' => '/',           // any path beginning and ending in /
            'userid' => $submission->userid,
            'author' => $USER->firstname . ' ' . $USER->lastname,
            'source' => $filename,
            'filename' => $filename
        );

        $fs->create_file_from_string($fileinfo, $file);

        $files = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id,
            'id',
            false
        );

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'pathnamehashes' => array_keys($files),
                'content' => '',
                //'format' => $data->pxaiwriter_editor['format']
            )
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        if ($this->assignment->is_blind_marking()) {
            $params['anonymous'] = 1;
        }
        $event = \assignsubmission_pxaiwriter\event\assessable_uploaded::create($params);
        $event->set_legacy_files($files);
        $event->trigger();

        $groupname = null;
        $groupid = 0;
        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        // Unset the objectid and other field from params for use in submission events.
        unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            // 'steps_data' => $count,
            'groupid' => $groupid,
            'groupname' => $groupname
        );

        if ($pxaiwritersubmission) { //when editing a submission
            $pxaiwritersubmission->steps_data = $data->assignsubmission_pxaiwriter_student_data;
            $params['objectid'] = $pxaiwritersubmission->id;
            $updatestatus = $DB->update_record('assignsubmission_pxaiwriter', $pxaiwritersubmission);
            $event = \assignsubmission_pxaiwriter\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else { // when it is new submission
            $pxaiwritersubmission = new stdClass();
            $pxaiwritersubmission->steps_data = $data->assignsubmission_pxaiwriter_student_data;
            $pxaiwritersubmission->submission = $submission->id;
            $pxaiwritersubmission->assignment = $this->assignment->get_instance()->id;
            $pxaiwritersubmission->id = $DB->insert_record('assignsubmission_pxaiwriter', $pxaiwritersubmission);
            $params['objectid'] = $pxaiwritersubmission->id;
            $event = \assignsubmission_pxaiwriter\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $pxaiwritersubmission->id > 0;
        }
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
     */
    public function remove(stdClass $submission)
    {
        global $DB;

        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            $reponse = $DB->delete_records('assignsubmission_pxaiwriter', array('submission' => $submissionid));
            if ($reponse) {
                $this->delete_pdf_file($submissionid);
            }
        }
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
            return "View Submission";
        } else {
            return "N/A";
        }
    }

    public function view(stdClass $submission)
    {
        $result = '';

        $subm = $this->get_pxaiwriter_submission($submission->id);
        if ($subm) {
            $stepsdata = json_decode($subm->steps_data);
            $stepsdatacount = count($stepsdata);
            $finalvalue = $stepsdata[$stepsdatacount - 1]->value;
            $result = str_replace("\n", "<br>", $finalvalue);
        }

        return $result;
    }

    // public function can_upgrade($type, $version)
    // {

    //     $uploadsingle_type = 'uploadsingle';
    //     $upload_type = 'upload';

    //     if (($type == $uploadsingle_type || $type == $upload_type) && $version >= 2011112900) {
    //         return true;
    //     }
    //     return false;
    // }

    // public function upgrade_settings(context $oldcontext, stdClass $oldassignment, &$log)
    // {
    //     global $DB;

    //     if ($oldassignment->assignmenttype == 'uploadsingle') {
    //         $this->set_config('maxfilesubmissions', 1);
    //         $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);
    //         return true;
    //     } else if ($oldassignment->assignmenttype == 'upload') {
    //         $this->set_config('maxfilesubmissions', $oldassignment->var1);
    //         $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);

    //         // Advanced file upload uses a different setting to do the same thing.                                                  
    //         $DB->set_field(
    //             'assign',
    //             'submissiondrafts',
    //             $oldassignment->var4,
    //             array('id' => $this->assignment->get_instance()->id)
    //         );

    //         // Convert advanced file upload "hide description before due date" setting.                                             
    //         $alwaysshow = 0;
    //         if (!$oldassignment->var3) {
    //             $alwaysshow = 1;
    //         }
    //         $DB->set_field(
    //             'assign',
    //             'alwaysshowdescription',
    //             $alwaysshow,
    //             array('id' => $this->assignment->get_instance()->id)
    //         );
    //         return true;
    //     }
    // }

    // public function upgrade($oldcontext, $oldassignment, $oldsubmission, $submission, &$log)
    // {
    //     global $DB;

    //     $file_submission = new stdClass();



    //     $file_submission->numfiles = $oldsubmission->numfiles;
    //     $file_submission->submission = $submission->id;
    //     $file_submission->assignment = $this->assignment->get_instance()->id;

    //     if (!$DB->insert_record('assign_submission_pxaiwriter', $file_submission) > 0) {
    //         $log .= get_string('couldnotconvertsubmission', 'assignsubmission_pxaiwriter', $submission->userid);
    //         return false;
    //     }




    //     // now copy the area files
    //     $this->assignment->copy_area_files_for_upgrade(
    //         $oldcontext->id,
    //         'mod_assignment',
    //         'submission',
    //         $oldsubmission->id,
    //         // New file area
    //         $this->assignment->get_context()->id,
    //         'mod_assign',
    //         ASSIGN_FILEAREA_SUBMISSION_FILES,
    //         $submission->id
    //     );

    //     return true;
    // }


    // public function get_editor_fields()
    // {
    //     return array('onlinetext' => get_string('pluginname', 'assignsubmission_pxaiwriter'));
    // }

    // public function get_editor_text($name, $submissionid)
    // {
    //     if ($name == 'onlinetext') {
    //         $onlinetextsubmission = $this->get_onlinetext_submission($submissionid);
    //         if ($onlinetextsubmission) {
    //             return $onlinetextsubmission->onlinetext;
    //         }
    //     }

    //     return '';
    // }


    // public function get_editor_format($name, $submissionid)
    // {
    //     if ($name == 'onlinetext') {
    //         $onlinetext_submission = $this->get_onlinetext_submission($submissionid);
    //         if ($onlinetext_submission) {
    //             return $onlinetext_submission->onlineformat;
    //         }
    //     }

    //     return 0;
    // }

    public function is_empty(stdClass $submission)
    {
        return false;
    }

    public function get_file_areas()
    {
        return array(ASSIGNSUBMISSION_PXAIWRITER_FILEAREA => $this->get_name());
    }

    // public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission)
    // {
    //     global $DB;

    //     // Copy the files across.                                                                                                   
    //     $contextid = $this->assignment->get_context()->id;
    //     $fs = get_file_storage();
    //     $files = $fs->get_area_files(
    //         $contextid,
    //         'assignsubmission_pxaiwriter',
    //         ASSIGNSUBMISSION_FILE_FILEAREA,
    //         $sourcesubmission->id,
    //         'id',
    //         false
    //     );
    //     foreach ($files as $file) {
    //         $fieldupdates = array('itemid' => $destsubmission->id);
    //         $fs->create_file_from_storedfile($fieldupdates, $file);
    //     }

    //     // Copy the assignsubmission_pxaiwriter record.                                                                                   
    //     if ($filesubmission = $this->get_file_submission($sourcesubmission->id)) {
    //         unset($filesubmission->id);
    //         $filesubmission->submission = $destsubmission->id;
    //         $DB->insert_record('assignsubmission_pxaiwriter', $filesubmission);
    //     }
    //     return true;
    // }

    public function format_for_log(stdClass $submission)
    {
        // format the info for each submission plugin add_to_log                                                                    

        $fileloginfo = '';
        $fileloginfo .= 'PXAIWriter submission.<br>';

        return $fileloginfo;
    }

    public function delete_instance()
    {
        global $DB;
        // will throw exception on failure                                                                                          
        $response = $DB->delete_records('assignsubmission_pxaiwriter', array('assignment' => $this->assignment->get_instance()->id));

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
     * @return void
     */
    public function getDiffRenderedHtml($textOne, $textTwo, $granularity = "word", $delReplaceTag = '<span style="color:red;background-color:#ffdddd;text-decoration:line-through;">', $insReplaceTag = '<span style="color:green;background-color:#ddffdd;text-decoration:none;">')
    {
        global $CFG;
        require_once("$CFG->dirroot/mod/assign/submission/pxaiwriter/vendor/autoload.php");

        $grOption = null;
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
}
