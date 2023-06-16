<?php

use assignsubmission_pxaiwriter\app\factory;

function xmldb_assignsubmission_pxaiwriter_upgrade($oldversion)
{
    if ($oldversion < 2023060100) {

        $factory = factory::make()->migration();
        $migrations = $factory->get_migrations_by_version(2023060100);
        foreach ($migrations as $migration)
        {
            $migration->up();
        }
    }

    return true;
}
