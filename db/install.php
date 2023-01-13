<?php

/**
 * Code run after the plugin database tables have been created.
 */
function xmldb_assignsubmission_pxaiwriter_install()
{
    global $CFG, $DB, $OUTPUT;

    require_once($CFG->dirroot . '/mod/assign/adminlib.php');
    $pluginmanager = new assign_plugin_manager('assignsubmission');

    $pluginmanager->move_plugin('pxaiwriter', 'up');
    $pluginmanager->move_plugin('pxaiwriter', 'up');

    return true;
}
