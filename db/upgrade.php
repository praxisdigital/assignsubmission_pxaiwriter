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

    return true;
}
