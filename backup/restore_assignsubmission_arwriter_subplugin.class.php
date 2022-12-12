<?php

defined('MOODLE_INTERNAL') || die();

class restore_assignsubmission_arwriter_subplugin extends restore_subplugin
{
    protected function define_submission_subplugin_structure()
    {

        $paths = array();

        $elename = $this->get_namefor('submission');

        // We used get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/submission_aiwriter');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    public function process_assignsubmission_aiwriter_submission($data)
    {
        global $DB;

        $data = (object)$data;
        $data->assignment = $this->get_new_parentid('assign');
        $oldsubmissionid = $data->submission;
        // The mapping is set in the restore for the core assign activity
        // when a submission node is processed.
        $data->submission = $this->get_mappingid('submission', $data->submission);

        $DB->insert_record('assignsubmission_aiwriter', $data);

        $this->add_related_files('assignsubmission_aiwriter', 'submissions_aiwriter', 'submission', null, $oldsubmissionid);
    }
}
