<?php

defined('MOODLE_INTERNAL') || die();

class restore_assignsubmission_pxaiwriter_subplugin extends restore_subplugin
{
    protected function define_submission_subplugin_structure()
    {

        $paths = array();

        $elename = $this->get_namefor('submission');

        // We used get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/submission_pxaiwriter');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    public function process_assignsubmission_pxaiwriter_submission($data)
    {
        global $DB;

        $data = (object)$data;
        $data->assignment = $this->get_new_parentid('assign');
        $oldsubmissionid = $data->submission;
        // The mapping is set in the restore for the core assign activity
        // when a submission node is processed.
        $data->submission = $this->get_mappingid('submission', $data->submission);

        $DB->insert_record('assignsubmission_pxaiwriter', $data);

        $this->add_related_files('assignsubmission_pxaiwriter', 'submissions_pxaiwriter', 'submission', null, $oldsubmissionid);
    }
}
