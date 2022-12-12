<?php

function xmldb_submission_pxaiwriter_upgrade($oldversion)
{
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();
    if ($oldversion < 2012091800) {
        // Put upgrade code here

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2012091800, 'assignsubmission', 'pxaiwriter');
    }

    return true;
}
