<?php

/**
 * Code run after the plugin database tables have been created.
 */
function xmldb_assignsubmission_aiwriter_install()
{
    global $CFG, $DB, $OUTPUT;

    // do the install

    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    // set the correct initial order for the plugins
    $assignment = new assignment();
    $plugin = $assignment->get_submission_plugin_by_type('aiwriter');
    if ($plugin) {
        $plugin->move('down');
        $plugin->move('down');
    }

    return true;
}
