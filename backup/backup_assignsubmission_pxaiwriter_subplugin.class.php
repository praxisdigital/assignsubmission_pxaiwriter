<?php

defined('MOODLE_INTERNAL') || die();

class backup_assignsubmission_pxaiwriter_subplugin extends backup_subplugin
{
    protected function define_submission_subplugin_structure()
    {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element(
            'submission_pxaiwriter',
            null,
            array('aiwriter', 'onlineformat', 'submission') // TODO Add all the table columns
        );

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // Set source to populate the data.
        $subpluginelement->set_source_table(
            'assignsubmission_pxaiwriter',
            array('submission' => backup::VAR_PARENTID)
        );

        $subpluginelement->annotate_files(
            'assignsubmission_pxaiwriter',
            'submissions_pxaiwriter',
            'submission'
        );
        return $subplugin;
    }
}
