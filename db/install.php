<?php

/**
 * Code run after the plugin database tables have been created.
 */
function xmldb_assignsubmission_pxaiwriter_install()
{
    global $CFG;

    require_once($CFG->dirroot . '/mod/assign/adminlib.php');
    $pluginmanager = new assign_plugin_manager('assignsubmission');

    $pluginmanager->move_plugin('pxaiwriter', 'up');
    $pluginmanager->move_plugin('pxaiwriter', 'up');

    // The AI writer needs configuring before it works, so it is off for new assignments
    // until an administrator turns it on.
    set_config('default', 0, 'assignsubmission_pxaiwriter');

    return true;
}
