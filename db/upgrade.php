<?php

use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models;
use assignsubmission_pxaiwriter\app\factory;

function xmldb_assignsubmission_pxaiwriter_upgrade($oldversion)
{
    $component = 'assignsubmission_pxaiwriter';

    if ($oldversion < 2023060100) {

        $factory = factory::make()->migration();
        $migrations = $factory->get_migrations_by_version(2023060100);
        foreach ($migrations as $migration)
        {
            $migration->up();
        }
    }

    if ($oldversion < 2024022300) {
        $setting_name = 'model';
        try {
            $old_model = get_config($component, $setting_name);
            if ($old_model !== models::GPT_3_5_TURBO) {
                set_config(
                    $setting_name,
                    models::GPT_3_5_TURBO,
                    $component
                );
            }
        }
        catch (Exception $e) {
            // Do nothing
        }
    }

    return true;
}
