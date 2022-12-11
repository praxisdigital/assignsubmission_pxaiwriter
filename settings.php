<?php
$settings->add(new admin_setting_configcheckbox(
    'assignsubmission_aiwriter/default',
    new lang_string('default', 'assignsubmission_aiwriter'),
    new lang_string('default_help', 'assignsubmission_aiwriter'),
    1
));

/**
 * id 
 * URL  
 * Content-Type  
 * authorization  
 * model  
 * temperature  
 * max_tokens  
 * top_p  
 * frequency_penalty 
 * presence_penalty  
 * API-key  
 * lastmodified 
 * lastmodifiedby 
 */

$settings->add(new admin_setting_heading(
    'assignsubmissionheading',
    new lang_string('open_ai_request_settings', 'assignsubmission_aiwriter'),
    new lang_string('open_ai_request_settings_description', 'assignsubmission_aiwriter'),
));

//URL
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/url',
    new lang_string('url', 'assignsubmission_aiwriter'),
    "", //new lang_string('url_description', 'assignsubmission_aiwriter'),
    ""
));
// Content Type
$settings->add(new admin_setting_configselect(
    'assignsubmission_aiwriter/content_type',
    new lang_string('content_type', 'assignsubmission_aiwriter'),
    "", //new lang_string('content_type_description', 'assignsubmission_aiwriter'),
    'json',
    ["json" => "JSON", "javascript" => "Javascript", 'text' => "Text", 'html' => "HTML", 'xml' => "XML"]
));

// authorization
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/authorization',
    new lang_string('authorization', 'assignsubmission_aiwriter'),
    "", //new lang_string('authorization_description', 'assignsubmission_aiwriter'),
    ""
));

// model
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/model',
    new lang_string('model', 'assignsubmission_aiwriter'),
    "", //new lang_string('model_description', 'assignsubmission_aiwriter'),
    ""
));

// temperature
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/temperature',
    new lang_string('temperature', 'assignsubmission_aiwriter'),
    "", //new lang_string('temperature_description', 'assignsubmission_aiwriter'),
    ""
));

// max_tokens
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/max_tokens',
    new lang_string('max_tokens', 'assignsubmission_aiwriter'),
    "", //new lang_string('max_tokens_description', 'assignsubmission_aiwriter'),
    999
));

// top_p
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/top_p',
    new lang_string('top_p', 'assignsubmission_aiwriter'),
    "", //new lang_string('top_p_description', 'assignsubmission_aiwriter'),
    ""
));

// frequency_penalty
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/frequency_penalty',
    new lang_string('frequency_penalty', 'assignsubmission_aiwriter'),
    "", //new lang_string('frequency_penalty_description', 'assignsubmission_aiwriter'),
    ""
));

// presence_penalty
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/presence_penalty',
    new lang_string('presence_penalty', 'assignsubmission_aiwriter'),
    "", //new lang_string('presence_penalty_description', 'assignsubmission_aiwriter'),
    ""

));

// API-key
$settings->add(new admin_setting_configtext(
    'assignsubmission_aiwriter/api_key',
    new lang_string('api_key', 'assignsubmission_aiwriter'),
    "", //new lang_string('api_key_description', 'assignsubmission_aiwriter'),
    ""
));

// lastmodifiedby
global $USER;
$settings->add(new admin_setting_configselect(
    'assignsubmission_aiwriter/last_modified_by',
    new lang_string('last_modified_by', 'assignsubmission_aiwriter'),
    "", //new lang_string('last_modified_by_description', 'assignsubmission_aiwriter'),
    $USER->id,
    [$USER->id => $USER->firstname . ' ' . $USER->lastname]
));
