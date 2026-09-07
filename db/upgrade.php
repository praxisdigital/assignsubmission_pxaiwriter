<?php

use assignsubmission_pxaiwriter\app\factory;

function xmldb_assignsubmission_pxaiwriter_upgrade($oldversion)
{
    global $DB;

    $component = 'assignsubmission_pxaiwriter';

    if ($oldversion < 2023060100) {

        $factory = factory::make()->migration();
        $migrations = $factory->get_migrations_by_version(2023060100);
        foreach ($migrations as $migration)
        {
            $migration->up();
        }
    }

    if ($oldversion < 2024050100) {
        try {
            // Reformat the response JSON object into array in AI writer history
            $sql = "UPDATE {pxaiwriter_history} SET
                    response = CONCAT('[', response, ']')
                    WHERE response IS NOT NULL";
            $DB->execute($sql);

            upgrade_plugin_savepoint(
                true,
                2024050100,
                'assignsubmission',
                'pxaiwriter'
            );
        }
        catch (Exception) {
            // Do nothing
        }
    }

    if ($oldversion < 2026090401) {
        // The AI writer needs configuring before it works, so it should not be on for new
        // assignments by default.
        //
        // This is applied unconditionally. Moodle writes the admin setting default into
        // config_plugins at install time, so an existing site stores '1' whether or not an
        // administrator ever chose it - the two cases are indistinguishable here. Any site
        // that deliberately wants it on has to re-enable it after this upgrade.
        set_config('default', 0, $component);

        upgrade_plugin_savepoint(
            true,
            2026090401,
            'assignsubmission',
            'pxaiwriter'
        );
    }

    return true;
}
