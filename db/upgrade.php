<?php

use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models;
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

    if ($oldversion < 2024050100) {
        $setting_name = 'model';
        try {
            $value = get_config($component, $setting_name);

            switch ($value) {
                case 'gpt-4-turbo':
                case 'gpt-4-turbo-preview':
                    $value = models::GPT_4_TURBO;
                    break;
                case 'gpt-4':
                case 'gpt-4-preview':
                    $value = models::GPT_4;
                    break;
                default:
                    $value = models::GPT_3_5_TURBO;
                    break;
            }

            // Force the model to be gpt-3.5-turbo, if the current model is deprecated
            set_config(
                $setting_name,
                $value,
                $component
            );

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
