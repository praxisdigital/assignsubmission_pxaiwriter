<?php

define('ASSIGNSUBMISSION_FILE_MAXFILES', 10);

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

        if ($aiwritersteps == null) {
            $description = get_string('first_step_description', 'assignsubmission_pxaiwriter');
            $step1 = new stdClass();
            $step1->step = 1;
            $step1->description = $description;
            $step1->mandatory = true;
            $step1->type = 'text';
            $step1->removable = false;

            $description = get_string('second_step_description', 'assignsubmission_pxaiwriter');
            $step2 = new stdClass();
            $step2->step = 2;
            $step2->description = $description;
            $step2->mandatory = true;
            $step2->type = 'text';
            $step1->removable = false;

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

    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data)
    {

        if ($this->get_config('maxfilesubmissions') <= 0) {
            return false;
        }

        $fileoptions = $this->get_file_options();
        $submissionid = $submission ? $submission->id : 0;

        $data = file_prepare_standard_filemanager(
            $data,
            'files',
            $fileoptions,
            $this->assignment->get_context(),
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_FILE_FILEAREA,
            $submissionid
        );
        $mform->addElement('filemanager', 'files_filemanager', html_writer::tag(
            'span',
            $this->get_name(),
            array('class' => 'accesshide')
        ), null, $fileoptions);

        return true;
    }

    public function save(stdClass $submission, stdClass $data)
    {
        global $USER, $DB;

        $fileoptions = $this->get_file_options();

        $data = file_postupdate_standard_filemanager(
            $data,
            'files',
            $fileoptions,
            $this->assignment->get_context(),
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_FILE_FILEAREA,
            $submission->id
        );

        $filesubmission = $this->get_file_submission($submission->id);

        // Plagiarism code event trigger when files are uploaded.                                                                   

        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_FILE_FILEAREA,
            $submission->id,
            'id',
            false
        );

        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_FILE_FILEAREA);

        // Send files to event system.                                                                                              
        // This lets Moodle know that an assessable file was uploaded (eg for plagiarism detection).                                
        $eventdata = new stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->assignment->get_course_module()->id;
        $eventdata->itemid = $submission->id;
        $eventdata->courseid = $this->assignment->get_course()->id;
        $eventdata->userid = $USER->id;
        if ($count > 1) {
            $eventdata->files = $files;
        }
        $eventdata->file = $files;
        $eventdata->pathnamehashes = array_keys($files);
        events_trigger('assessable_file_uploaded', $eventdata);

        if ($filesubmission) {
            $filesubmission->numfiles = $this->count_files(
                $submission->id,
                ASSIGNSUBMISSION_FILE_FILEAREA
            );
            return $DB->update_record('assignsubmission_pxaiwriter', $filesubmission);
        } else {
            $filesubmission = new stdClass();
            $filesubmission->numfiles = $this->count_files(
                $submission->id,
                ASSIGNSUBMISSION_FILE_FILEAREA
            );
            $filesubmission->submission = $submission->id;
            $filesubmission->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignsubmission_pxaiwriter', $filesubmission) > 0;
        }
    }

    // public function get_files($submission, $class)
    // {
    //     $result = array();
    //     $fs = get_file_storage();

    //     $files = $fs->get_area_files(
    //         $this->assignment->get_context()->id,
    //         'assignsubmission_pxaiwriter',
    //         ASSIGNSUBMISSION_FILE_FILEAREA,
    //         $submission->id,
    //         'timemodified',
    //         false
    //     );

    //     foreach ($files as $file) {
    //         $result[$file->get_filename()] = $file;
    //     }
    //     return $result;
    // }

    public function view_summary(stdClass $submission, &$showviewlink)
    {
        $subm = $this->get_pxaiwriter_submission($submission->id);

        if ($subm) {
            $showviewlink = true;
            return "Last submission date : ";
        } else {
            return "N/A";
        }
        // $count = $this->count_files($submission->id, ASSIGNSUBMISSION_FILE_FILEAREA);

        // // Show we show a link to view all files for this plugin?                                                                   
        // $showviewlink = $count > ASSIGNSUBMISSION_PXAIWRITER_MAXSUMMARYFILES;
        // if ($count <= ASSIGNSUBMISSION_PXAIWRITER_MAXSUMMARYFILES) {
        //     return $this->assignment->render_area_files(
        //         'assignsubmission_pxaiwriter',
        //         ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
        //         $submission->id
        //     );
        // } else {
        //     return get_string('countfiles', 'assignsubmission_pxaiwriter', $count);
        // }
    }

    public function view($submission)
    {
        return $this->assignment->render_editor_content(
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id
        );
    }

    public function can_upgrade($type, $version)
    {

        $uploadsingle_type = 'uploadsingle';
        $upload_type = 'upload';

        if (($type == $uploadsingle_type || $type == $upload_type) && $version >= 2011112900) {
            return true;
        }
        return false;
    }

    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, &$log)
    {
        global $DB;

        if ($oldassignment->assignmenttype == 'uploadsingle') {
            $this->set_config('maxfilesubmissions', 1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);
            return true;
        } else if ($oldassignment->assignmenttype == 'upload') {
            $this->set_config('maxfilesubmissions', $oldassignment->var1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);

            // Advanced file upload uses a different setting to do the same thing.                                                  
            $DB->set_field(
                'assign',
                'submissiondrafts',
                $oldassignment->var4,
                array('id' => $this->assignment->get_instance()->id)
            );

            // Convert advanced file upload "hide description before due date" setting.                                             
            $alwaysshow = 0;
            if (!$oldassignment->var3) {
                $alwaysshow = 1;
            }
            $DB->set_field(
                'assign',
                'alwaysshowdescription',
                $alwaysshow,
                array('id' => $this->assignment->get_instance()->id)
            );
            return true;
        }
    }

    public function upgrade($oldcontext, $oldassignment, $oldsubmission, $submission, &$log)
    {
        global $DB;

        $file_submission = new stdClass();



        $file_submission->numfiles = $oldsubmission->numfiles;
        $file_submission->submission = $submission->id;
        $file_submission->assignment = $this->assignment->get_instance()->id;

        if (!$DB->insert_record('assign_submission_pxaiwriter', $file_submission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'assignsubmission_pxaiwriter', $submission->userid);
            return false;
        }




        // now copy the area files
        $this->assignment->copy_area_files_for_upgrade(
            $oldcontext->id,
            'mod_assignment',
            'submission',
            $oldsubmission->id,
            // New file area
            $this->assignment->get_context()->id,
            'mod_assign',
            ASSIGN_FILEAREA_SUBMISSION_FILES,
            $submission->id
        );

        return true;
    }


    public function get_editor_fields()
    {
        return array('onlinetext' => get_string('pluginname', 'assignsubmission_pxaiwriter'));
    }

    public function get_editor_text($name, $submissionid)
    {
        if ($name == 'onlinetext') {
            $onlinetextsubmission = $this->get_onlinetext_submission($submissionid);
            if ($onlinetextsubmission) {
                return $onlinetextsubmission->onlinetext;
            }
        }

        return '';
    }


    public function get_editor_format($name, $submissionid)
    {
        if ($name == 'onlinetext') {
            $onlinetext_submission = $this->get_onlinetext_submission($submissionid);
            if ($onlinetext_submission) {
                return $onlinetext_submission->onlineformat;
            }
        }

        return 0;
    }

    public function is_empty(stdClass $submission)
    {
        $subm = $this->get_pxaiwriter_submission($submission->id);
        return $subm ? true : false;
    }

    public function get_file_areas()
    {
        return array(ASSIGNSUBMISSION_FILE_FILEAREA => $this->get_name());
    }

    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission)
    {
        global $DB;

        // Copy the files across.                                                                                                   
        $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $contextid,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_FILE_FILEAREA,
            $sourcesubmission->id,
            'id',
            false
        );
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

        // Copy the assignsubmission_pxaiwriter record.                                                                                   
        if ($filesubmission = $this->get_file_submission($sourcesubmission->id)) {
            unset($filesubmission->id);
            $filesubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_pxaiwriter', $filesubmission);
        }
        return true;
    }

    public function format_for_log(stdClass $submission)
    {
        // format the info for each submission plugin add_to_log                                                                    
        $filecount = $this->count_files($submission->id, ASSIGNSUBMISSION_FILE_FILEAREA);
        $fileloginfo = '';
        $fileloginfo .= ' the number of file(s) : ' . $filecount . " file(s).<br>";

        return $fileloginfo;
    }

    public function delete_instance()
    {
        global $DB;
        // will throw exception on failure                                                                                          
        $DB->delete_records('assignsubmission_pxaiwriter', array('assignment' => $this->assignment->get_instance()->id));

        return true;
    }
}
