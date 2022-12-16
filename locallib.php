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

    private function get_pxaiwriter_submission($submissionid)
    {
        global $DB;

        $xxxx = $DB->get_record('assignsubmission_pxaiwriter', array('submission' => $submissionid));
        //echo(var_dump($xxxx->step_data));
        return $xxxx;
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

        if ($aiwritersteps == null) {
            $description = get_string('first_step_description', 'assignsubmission_pxaiwriter');

            $step1 = new stdClass();
            $step1->step = 1;
            $step1->description = $description;
            $step1->mandatory = true;
            $step1->type = 'text';
            $step1->removable = false;
            $step1->custom_buttons = ['do_ai_magic', 'expand'];
            $step1->value = '';

            $description = get_string('second_step_description', 'assignsubmission_pxaiwriter');
            $step2 = new stdClass();
            $step2->step = 2;
            $step2->description = $description;
            $step2->mandatory = true;
            $step2->type = 'text';
            $step2->removable = false;
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
        global $CFG;
        $elements = array();

        $editoroptions = $this->get_edit_options();
        $submissionid = $submission ? $submission->id : 0;

        // if (!isset($data->steps_data)) {
        //     $data->steps_data = '';
        // }
        // if (!isset($data->pxaiwriterformat)) {
        //     $data->pxaiwriterformat = editors_get_preferred_format();
        // }

        $data->steps_data = json_decode($this->get_config('pxaiwritersteps'));

        if ($submission) {
            $pxaiwritersubmission = $this->get_pxaiwriter_submission($submission->id);
            if ($pxaiwritersubmission) {
                $data->steps_data =  json_decode($pxaiwritersubmission->steps_data);
                //$data->pxaiwriterformat = $pxaiwritersubmission->pxaiwriterformat;
            }
        }

        // $data = file_prepare_standard_editor(
        //     $data,
        //     'steps_data',
        //     $editoroptions,
        //     $this->assignment->get_context(),
        //     'assignsubmission_pxaiwriter',
        //     ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
        //     $submissionid
        // );

        // echo("here the elements are adding to the form");
        // $mform->addElement('editor', 'pxaiwriter_editor', $this->get_name(), null, $editoroptions);

        // $data->steps_data = [
        //     array(
        //         "step" => 1,
        //         "description" =>  "the description 1 for this!",
        //         "mandatory" => "",
        //         'type' =>  "",
        //         "removable" =>  false,
        //         "custom_buttons" =>  ['do_ai_magic', 'expand'],
        //         "value" => "",
        //     ),
        //     array(
        //         "step" => 2,
        //         "description" =>  "the description 2 for this!",
        //         "mandatory" =>  "",
        //         'type' =>  "",
        //         "removable" =>  false,
        //         "value" => "",
        //     ),
        //     array(
        //         "step" =>  3,
        //         "description" =>  "the description 3 for this!",
        //         "mandatory" =>  "",
        //         'type' =>  "",
        //         "removable" =>  true,
        //         "value" => "",
        //     ),
        // ];

        MoodleQuickForm::registerElementType(
            'pxaiwriter_steps_section',
            "$CFG->dirroot/mod/assign/submission/pxaiwriter/classes/pxaiwriter_steps_student_form_element.php",
            'pxaiwriter_steps_student_form_element'
        );

        $mform->addElement('pxaiwriter_steps_section', 'assignsubmission_pxaiwriter_steps_config', null, null, $data);

        return true;
    }

    public function save(stdClass $submission, stdClass $data)
    {
        global $USER, $DB;

        $editoroptions = $this->get_edit_options();

        $data = file_postupdate_standard_editor(
            $data,
            'steps_data',
            $editoroptions,
            $this->assignment->get_context(),
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id
        );

        //echo(var_dump($data));

        $pxaiwritersubmission = $this->get_pxaiwriter_submission($submission->id);

        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id,
            'id',
            false
        );

        // Check word count before submitting anything.
        // $exceeded = $this->check_word_count(trim($data->pxaiwriter));
        // if ($exceeded) {
        //     $this->set_error($exceeded);
        //     return false;
        // }

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'pathnamehashes' => array_keys($files),
                'content' => trim($data->steps_data),
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

        // $count = count_words($data->onlinetext);

        // Unset the objectid and other field from params for use in submission events.
        unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            // 'onlinetextwordcount' => $count,
            'groupid' => $groupid,
            'groupname' => $groupname
        );

        if ($pxaiwritersubmission) {

            $pxaiwritersubmission->steps_data = '345'; //$data->pxaiwriter_editor['text'];
            //$pxaiwritersubmission->pxaiwriterformat = $data->pxaiwriter_editor['format'];
            $params['objectid'] = $pxaiwritersubmission->id;
            $updatestatus = $DB->update_record('assignsubmission_pxaiwriter', $pxaiwritersubmission);
            $event = \assignsubmission_pxaiwriter\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else {
            echo (var_dump($data->pxaiwriter_editor['text']));
            $pxaiwritersubmission = new stdClass();
            $pxaiwritersubmission->steps_data = 'fshfsHF'; //$data->pxaiwriter_editor['text'];
            //$pxaiwritersubmission->pxaiwriterformat = $data->pxaiwriter_editor['format'];

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

    public function submission_is_empty(stdClass $data)
    {
        // if (!isset($data->onlinetext_editor)) {
        //     return true;
        // }
        // $wordcount = 0;
        // $hasinsertedresources = false;

        // if (isset($data->onlinetext_editor['text'])) {
        //     $wordcount = count_words(trim((string)$data->onlinetext_editor['text']));
        //     // Check if the online text submission contains video, audio or image elements
        //     // that can be ignored and stripped by count_words().
        //     $hasinsertedresources = preg_match('/<\s*((video|audio)[^>]*>(.*?)<\s*\/\s*(video|audio)>)|(img[^>]*>(.*?))/',
        //             trim((string)$data->onlinetext_editor['text']));
        // }

        // return $wordcount == 0 && !$hasinsertedresources;
        return false;
    }


    // public function save(stdClass $submission, stdClass $data)
    // {
    //     global $USER, $DB;

    //     $fileoptions = $this->get_file_options();

    //     $data = file_postupdate_standard_filemanager(
    //         $data,
    //         'files',
    //         $fileoptions,
    //         $this->assignment->get_context(),
    //         'assignsubmission_pxaiwriter',
    //         ASSIGNSUBMISSION_FILE_FILEAREA,
    //         $submission->id
    //     );

    //     $filesubmission = $this->get_file_submission($submission->id);

    //     // Plagiarism code event trigger when files are uploaded.                                                                   

    //     $fs = get_file_storage();
    //     $files = $fs->get_area_files(
    //         $this->assignment->get_context()->id,
    //         'assignsubmission_pxaiwriter',
    //         ASSIGNSUBMISSION_FILE_FILEAREA,
    //         $submission->id,
    //         'id',
    //         false
    //     );

    //     $count = $this->count_files($submission->id, ASSIGNSUBMISSION_FILE_FILEAREA);

    //     // Send files to event system.                                                                                              
    //     // This lets Moodle know that an assessable file was uploaded (eg for plagiarism detection).                                
    //     $eventdata = new stdClass();
    //     $eventdata->modulename = 'assign';
    //     $eventdata->cmid = $this->assignment->get_course_module()->id;
    //     $eventdata->itemid = $submission->id;
    //     $eventdata->courseid = $this->assignment->get_course()->id;
    //     $eventdata->userid = $USER->id;
    //     if ($count > 1) {
    //         $eventdata->files = $files;
    //     }
    //     $eventdata->file = $files;
    //     $eventdata->pathnamehashes = array_keys($files);
    //     events_trigger('assessable_file_uploaded', $eventdata);

    //     if ($filesubmission) {
    //         $filesubmission->numfiles = $this->count_files(
    //             $submission->id,
    //             ASSIGNSUBMISSION_FILE_FILEAREA
    //         );
    //         return $DB->update_record('assignsubmission_pxaiwriter', $filesubmission);
    //     } else {
    //         $filesubmission = new stdClass();
    //         $filesubmission->numfiles = $this->count_files(
    //             $submission->id,
    //             ASSIGNSUBMISSION_FILE_FILEAREA
    //         );
    //         $filesubmission->submission = $submission->id;
    //         $filesubmission->assignment = $this->assignment->get_instance()->id;
    //         return $DB->insert_record('assignsubmission_pxaiwriter', $filesubmission) > 0;
    //     }
    // }

    public function get_files($submission, $class)
    {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_FILE_FILEAREA,
            $submission->id,
            'timemodified',
            false
        );

        foreach ($files as $file) {
            $result[$file->get_filename()] = $file;
        }
        return $result;
    }

    public function view_summary(stdClass $submission, &$showviewlink)
    {
        $subm = $this->get_pxaiwriter_submission($submission->id);
        //echo(var_dump($subm));
        if ($subm) {
            $showviewlink = true;
            return $submission->id;
        } else {
            return "N/A";
        }
    }

    public function view(stdClass $submission)
    {
        global $CFG;
        $result = '';

        $subm = $this->get_pxaiwriter_submission($submission->id);
        if ($subm) {
            $plagiarismlinks = $subm->steps_data;
            //$plagiarismlinks = $plagiarismlinks .' '. $subm->assignment;
            //$plagiarismlinks = $plagiarismlinks .' '. $subm->steps_data;
        } else {
            $plagiarismlinks = 5555;
        }


        //$pxaiwritersubmission = $this->get_pxaiwriter_submission($submission->id);

        // if ($pxaiwritersubmission) {

        //     // Render for portfolio API.
        //     $result .= $this->assignment->render_editor_content(ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
        //                                                         $pxaiwritersubmission->submission,
        //                                                         $this->get_type(),
        //                                                         'steps_data',
        //                                                         'assignsubmission_pxaiwriter');

        //     // if (!empty($CFG->enableplagiarism)) {
        //     //     require_once($CFG->libdir . '/plagiarismlib.php');

        //     //     $plagiarismlinks .= plagiarism_get_links(array('userid' => $submission->userid,
        //     //         'content' => trim($onlinetextsubmission->onlinetext),
        //     //         'cmid' => $this->assignment->get_course_module()->id,
        //     //         'course' => $this->assignment->get_course()->id,
        //     //         'assignment' => $submission->assignment));
        //     // }
        // }

        return $plagiarismlinks . $result;
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

    // public function get_file_areas()
    // {
    //     return array(ASSIGNSUBMISSION_FILE_FILEAREA => $this->get_name());
    // }

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

    // public function format_for_log(stdClass $submission)
    // {
    //     // format the info for each submission plugin add_to_log                                                                    
    //     $filecount = $this->count_files($submission->id, ASSIGNSUBMISSION_FILE_FILEAREA);
    //     $fileloginfo = '';
    //     $fileloginfo .= ' the number of file(s) : ' . $filecount . " file(s).<br>";

    //     return $fileloginfo;
    // }

    // public function delete_instance()
    // {
    //     global $DB;
    //     // will throw exception on failure                                                                                          
    //     $DB->delete_records('assignsubmission_pxaiwriter', array('assignment' => $this->assignment->get_instance()->id));

    //     return true;
    // }

}
