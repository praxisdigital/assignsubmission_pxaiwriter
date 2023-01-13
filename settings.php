<?php
$settings->add(new admin_setting_configcheckbox(
    'assignsubmission_pxaiwriter/default',
    new lang_string('default', 'assignsubmission_pxaiwriter'),
    new lang_string('default_help', 'assignsubmission_pxaiwriter'),
    1
));

$settings->add(new admin_setting_heading(
    'assignsubmissionheading',
    new lang_string('open_ai_request_settings', 'assignsubmission_pxaiwriter'),
    new lang_string('open_ai_request_settings_description', 'assignsubmission_pxaiwriter'),
));

//URL
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/url',
    new lang_string('url', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('url_description', 'assignsubmission_pxaiwriter'),
    ""
));
// Content Type
$settings->add(new admin_setting_configselect(
    'assignsubmission_pxaiwriter/content_type',
    new lang_string('content_type', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('content_type_description', 'assignsubmission_pxaiwriter'),
    'json',
    ["application/json" => "JSON", "application/javascript" => "Javascript", 'application/text' => "Text", 'application/html' => "HTML", 'application/xml' => "XML"]
));

// authorization
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/authorization',
    new lang_string('authorization', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('authorization_description', 'assignsubmission_pxaiwriter'),
    ""
));

// model
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/model',
    new lang_string('model', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('model_description', 'assignsubmission_pxaiwriter'),
    ""
));

// temperature
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/temperature',
    new lang_string('temperature', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('temperature_description', 'assignsubmission_pxaiwriter'),
    ""
));

// max_tokens
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/max_tokens',
    new lang_string('max_tokens', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('max_tokens_description', 'assignsubmission_pxaiwriter'),
    999
));

// top_p
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/top_p',
    new lang_string('top_p', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('top_p_description', 'assignsubmission_pxaiwriter'),
    ""
));

// frequency_penalty
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/frequency_penalty',
    new lang_string('frequency_penalty', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('frequency_penalty_description', 'assignsubmission_pxaiwriter'),
    ""
));

// presence_penalty
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/presence_penalty',
    new lang_string('presence_penalty', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('presence_penalty_description', 'assignsubmission_pxaiwriter'),
    ""

));

// API-key
$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/api_key',
    new lang_string('api_key', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('api_key_description', 'assignsubmission_pxaiwriter'),
    ""
));

// lastmodifiedby
global $USER;
$settings->add(new admin_setting_configselect(
    'assignsubmission_pxaiwriter/last_modified_by',
    new lang_string('last_modified_by', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('last_modified_by_description', 'assignsubmission_pxaiwriter'),
    $USER->id,
    [$USER->id => $USER->firstname . ' ' . $USER->lastname]
));

$settings->add(new admin_setting_heading(
    'assignsubmissioncomparerheading',
    new lang_string('open_ai_comparer_settings', 'assignsubmission_pxaiwriter'),
    new lang_string('open_ai_comparer_settings_description', 'assignsubmission_pxaiwriter'),
));

$settings->add(new admin_setting_configselect(
    'assignsubmission_pxaiwriter/granularity',
    new lang_string('granularity', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('content_type_description', 'assignsubmission_pxaiwriter'),
    'json',
    ["word" => "Word", "sentence" => "Sentence", 'paragraph' => "Paragraph", 'character' => "Character"]
));

$settings->add(new admin_setting_heading(
    'assignsubmissionassignmentheading',
    new lang_string('open_ai_assignment_settings', 'assignsubmission_pxaiwriter'),
    new lang_string('open_ai_assignment_settings_description', 'assignsubmission_pxaiwriter'),
));

$settings->add(new admin_setting_configtext(
    'assignsubmission_pxaiwriter/attempt_count',
    new lang_string('attempt_count', 'assignsubmission_pxaiwriter'),
    "", //new lang_string('url_description', 'assignsubmission_pxaiwriter'),
    ""
));
