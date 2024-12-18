<?php

use assignsubmission_pxaiwriter\app\factory as factory;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models as openai_models;

/**
 * @var admin_settingpage $settings
 */
$settings->add(new admin_setting_configcheckbox(
    'assignsubmission_pxaiwriter/default',
    new lang_string('default', 'assignsubmission_pxaiwriter'),
    new lang_string('default_help', 'assignsubmission_pxaiwriter'),
    1
));

// Assignment settings
$settings->add(new admin_setting_heading(
    'assignsubmissionassignmentheading',
    new lang_string('open_ai_assignment_settings', 'assignsubmission_pxaiwriter'),
    new lang_string('open_ai_assignment_settings_description', 'assignsubmission_pxaiwriter'),
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/attempt_count',
    new lang_string('attempt_count', 'assignsubmission_pxaiwriter'),
    new lang_string('attempt_count_description', 'assignsubmission_pxaiwriter'),
    2,
    PARAM_INT
));

// OpenAI settings
$settings->add(new admin_setting_heading(
    'assignsubmissionheading',
    new lang_string('open_ai_request_settings', 'assignsubmission_pxaiwriter'),
    new lang_string('open_ai_request_settings_description', 'assignsubmission_pxaiwriter'),
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/openai_token',
    new lang_string('openai_token', 'assignsubmission_pxaiwriter'),
    new lang_string('openai_token_description', 'assignsubmission_pxaiwriter'),
    ""
));

$settings->add(new admin_setting_configselect(
    'assignsubmission_pxaiwriter/model',
    new lang_string('model', 'assignsubmission_pxaiwriter'),
    new lang_string('model_description', 'assignsubmission_pxaiwriter'),
    openai_models::GPT_3_5_TURBO,
    factory::make()->ai()->openai()->models()->get_models_list()
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/temperature',
    new lang_string('temperature', 'assignsubmission_pxaiwriter'),
    new lang_string('temperature_description', 'assignsubmission_pxaiwriter'),
    "0.7"
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/max_tokens',
    new lang_string('max_tokens', 'assignsubmission_pxaiwriter'),
    new lang_string('max_tokens_description', 'assignsubmission_pxaiwriter'),
    5000
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/top_p',
    new lang_string('top_p', 'assignsubmission_pxaiwriter'),
    new lang_string('top_p_description', 'assignsubmission_pxaiwriter'),
    "1"
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/frequency_penalty',
    new lang_string('frequency_penalty', 'assignsubmission_pxaiwriter'),
    new lang_string('frequency_penalty_description', 'assignsubmission_pxaiwriter'),
    "0"
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/presence_penalty',
    new lang_string('presence_penalty', 'assignsubmission_pxaiwriter'),
    new lang_string('presence_penalty_description', 'assignsubmission_pxaiwriter'),
    "0"

));
