<?php

/** @global admin_settingpage $settings */
/** @global admin_root $ADMIN */
global $ADMIN;

if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configcheckbox(
            'assignsubmission_pxaiwriter/default',
            new lang_string('default', 'assignsubmission_pxaiwriter'),
            new lang_string('default_help', 'assignsubmission_pxaiwriter'),
            0
        )
    );

    // Assignment settings
    $settings->add(
        new admin_setting_configtext(
            'assignsubmission_pxaiwriter/attempt_count',
            new lang_string('attempt_count', 'assignsubmission_pxaiwriter'),
            new lang_string('attempt_count_description', 'assignsubmission_pxaiwriter'),
            2,
            PARAM_INT
        )
    );
}