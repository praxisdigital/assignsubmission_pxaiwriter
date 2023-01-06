<?php

define('ASSIGNSUBMISSION_FILE_MAXFILES', 10);
define('ASSIGNSUBMISSION_PXAIWRITER_FILEAREA', 'submissions_pxaiwriter');
//require_once '/app/vendor/autoload.php';

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
        global $CFG;
        $elements = array();

        $editoroptions = $this->get_edit_options();
        $submissionid = $submission ? $submission->id : 0;

<<<<<<< HEAD
=======
        $data = file_prepare_standard_filemanager(
            $data,
            'setps_data_file',
            $editoroptions,
            $this->assignment->get_context(),
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submissionid
        );

        // if (!isset($data->steps_data)) {
        //     $data->steps_data = '';
        // }
        // if (!isset($data->pxaiwriterformat)) {
        //     $data->pxaiwriterformat = editors_get_preferred_format();
        // }



        // if ($submission) {
>>>>>>> 94ff3c8d536f6727e8796a4920c0d5d55b57ff02
        $pxaiwritersubmission = $this->get_pxaiwriter_submission($submission->id);
        if ($pxaiwritersubmission) {
            $data->steps_data =  json_decode($pxaiwritersubmission->steps_data);
            //$data->pxaiwriterformat = $pxaiwritersubmission->pxaiwriterformat;
        } else {
            $data->steps_data = json_decode($this->get_config('pxaiwritersteps'));
        }

        MoodleQuickForm::registerElementType(
            'pxaiwriter_steps_section',
            "$CFG->dirroot/mod/assign/submission/pxaiwriter/classes/pxaiwriter_steps_student_form_element.php",
            'pxaiwriter_steps_student_form_element'
        );

        // $data = file_prepare_standard_filemanager($data,
        //     'steps_data_file',
        //     $editoroptions,
        //     $this->assignment->get_context(),
        //     'assignsubmission_pxaiwriter',
        //     ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
        //     $submission->id);

        $mform->addElement('pxaiwriter_steps_section', 'assignsubmission_pxaiwriter_steps_config', null, null, $data);

        $mform->addElement('hidden', 'assignsubmission_pxaiwriter_student_data', null);
        $mform->setType('assignsubmission_pxaiwriter_student_data', PARAM_RAW);

        return true;
    }

    public function save(stdClass $submission, stdClass $data)
    {
        global $USER, $DB, $CFG;

        //$editoroptions = $this->get_edit_options();

<<<<<<< HEAD
        $assignmentid = $this->get_assignment_id();
        $filename = $this->get_pdf_file_name($assignmentid, $USER->id);

        /*require_once ('/php-finediff/src/Diff.php');
        $diff = new FineDiff\Diff();
        echo $diff->render('string one', 'string two');*/

        require_once ($CFG->libdir . '/tcpdf/tcpdf.php');

        $pdf = new TCPDF();
        $pdf->AddPage();
        $html = <<<EOD
        <h1>Welcome 12345678901 to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
        <i>This is the first example of TCPDF library.</i>
        <p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
        <p>Please check the source code documentation and other examples for further information.</p>
        <p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
        EOD;

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $file = $pdf->Output($filename,'S');
        //echo(var_dump($fileC));

        // $data = file_postupdate_standard_editor(
        //     $data,
        //     'steps_data_file',
        //     $editoroptions,
        //     $this->assignment->get_context(),
        //     'assignsubmission_pxaiwriter',
        //     ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
        //     $submission->id
        // );
=======
        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/temp']);
        $mpdf->WriteHTML('Hello World');
        // Other code
        //$mpdf->Output();
        echo (var_dump($mpdf));
        $data->setps_data_file_filemanager = $mpdf;

        $data = file_postupdate_standard_editor(
            $data,
            'setps_data_file',
            $editoroptions,
            $this->assignment->get_context(),
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id
        );
>>>>>>> 94ff3c8d536f6727e8796a4920c0d5d55b57ff02

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


        // Prepare file record object
        $fileinfo = array(
            'contextid' => $this->assignment->get_context()->id, // ID of context
            'component' => 'assignsubmission_pxaiwriter',     // usually = table name
            'filearea' => ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,     // usually = table name
            'itemid' => $submission->id,               // usually = ID of row in table
            'filepath' => '/',           // any path beginning and ending in /
            'userid' => $submission->userid,
            'author' => $USER->name,
            'source' => $filename,
            'filename' => $filename); // any filename

        $fs->create_file_from_string($fileinfo, $file);
        
        $files = $fs->get_area_files(
            $this->assignment->get_context()->id,
            'assignsubmission_pxaiwriter',
            ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
            $submission->id,
            'id',
            false
        );
        
        // foreach($files as $fs) {
        //     $contents = $fs->get_content();
        //     echo(var_dump($contents));
        // }

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

        if ($pxaiwritersubmission) {
            $pxaiwritersubmission->steps_data = $data->assignsubmission_pxaiwriter_student_data;
            $params['objectid'] = $pxaiwritersubmission->id;
            $updatestatus = $DB->update_record('assignsubmission_pxaiwriter', $pxaiwritersubmission);
            $event = \assignsubmission_pxaiwriter\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else {
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

    private function get_pdf_file_name(int $assignmentid, int $userid) {
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
            $DB->delete_records('assignsubmission_pxaiwriter', array('submission' => $submissionid));
        }
        return true;
    }


    public function get_files(stdClass $submission, stdClass $user) {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_pxaiwriter',
                                     ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
                                     $submission->id,
                                     'timemodified',
                                     false);
        echo("here");
        echo(var_dump($files));

        foreach ($files as $file) {
            // Do we return the full folder path or just the file name?
            if (isset($submission->exportfullpath) && $submission->exportfullpath == false) {
                $result[$file->get_filename()] = $file;
            } else {
                $result[$file->get_filepath().$file->get_filename()] = $file;
            }
        }
        return $result;
    }

    public function view_summary(stdClass $submission, &$showviewlink)
    {
        $subm = $this->get_pxaiwriter_submission($submission->id);
        //echo(var_dump($subm));
        if ($subm) {
            $showviewlink = true;
            return "View Submission";
        } else {
            return "N/A";
        }
    }

    public function view(stdClass $submission)
    {
        global $CFG;
        $result = '';
        return $this->assignment->render_area_files('assignsubmission_pxaiwriter',
                                       ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
                                                   $submission->id);

        // $subm = $this->get_pxaiwriter_submission($submission->id);
        // if ($subm) {
        //     $plagiarismlinks = $subm->steps_data;
        //     //$plagiarismlinks = $plagiarismlinks .' '. $subm->assignment;
        //     //$plagiarismlinks = $plagiarismlinks .' '. $subm->steps_data;
        // } else {
        //     $plagiarismlinks = 5555;
        // }


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

        //return $plagiarismlinks . $result;
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
