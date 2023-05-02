<?php

$string['pluginname'] = 'AI writer submissions';
$string['enabled'] = "Enabled";
$string['enabled_help'] = "Enabled help";
$string['default_help'] = "Default Help";
$string['default'] = "Default";

$string['guide_to_step_label'] = "Guide to step";
$string['add_step_label'] = "Add another step";
$string['remove_step_label'] = "Delete step";
$string['submission_step_label'] = "AI writer submission steps";
$string['ai_writer_helper_msg'] = "Help with AI writer submission steps";
$string['ai_writer_helper_msg_description'] = "If AI writer submissions are enabled, each student will be able to follow these steps in their submission.";

$string['first_step_description'] = "In this step you have to write some keywords to generate a text to work with. In the next step, you must validate the grammar and examine whether the claims in the text are correct.";
$string['second_step_description'] = "In this step you can edit and improve the text by validating the grammar and examining whether the claims in the text are correct.";

$string['steps_change_warning'] = "These steps are already used in assignment submission. Your changes will be applied to the future submissions of same assignment. Do you want to continue?";
$string['ok_button_label'] = "OK";
$string['cancel_button_label'] = "Cancel";
$string['close_button_label'] = "Close";
$string['open_ai_request_settings'] = "Open API request settings";
$string['open_ai_request_settings_description'] = "";

$string['open_ai_comparer_settings'] = "Open API text comparer settings";
$string['open_ai_comparer_settings_description'] = "";

$string['url'] = "URL";
$string['url_description'] = "Open API URL";

$string['content_type'] = 'Content Type';
$string['content_type_description'] = "";
$string['openai_token'] = 'OpenAI API Token';
$string['openai_token_description'] = 'Set your OpenAI API Token. Example: sk-{token}';

$string['model'] = 'Model';
$string['model_description'] = "";
$string['temperature'] = "Temperature";
$string['temperature_description'] = "";

$string['max_tokens'] = "Max tokens";
$string['max_tokens_description'] = "";

$string['top_p'] = "Top p";
$string['top_p_description'] = "";

$string['frequency_penalty'] = "Frequency Penalty";
$string['frequency_penalty_description'] = "";

$string['presence_penalty'] = "Presence penalty";
$string['presence_penalty_description'] = "";

$string['api_key'] = "API key";
$string['api_key_description'] = "";

$string['last_modified'] = "Last modified";
$string['last_modified_description'] = "";

$string['last_modified_by'] = "Last modified by";
$string['last_modified_by_description'] = "";

$string['steps_title'] = "Step";
$string['previous'] = "Previous";
$string['next'] = "Next";
$string['expand_selection'] = "Expand selection";
$string['do_ai_magic'] = "Do AI magic";

$string['title_place_holder'] = "Write an instruction to the AI: ex. 'Write an essay on computers'";
$string['title-required-warning'] = "Title is a mandatory field for this action. Please insert an appropriate title to proceed.";

$string['selection-required-warning'] = "Please select a valid phrase or text with in the text container to proceed with this action.";

$string['granularity'] = "Granularity";
$string['granularity_description'] = "";

$string['open_ai_assignment_settings'] = "Assignment Settings";
$string['open_ai_assignment_settings_description'] = "";


$string["attempt_count"] = "Attempt count";
$string["attempt_count_description"] = "";

$string["submission_due_msg"] = "Your assignment submission is due. You are restricted from using the AI feature for this submission.";
$string["ai_attempt_exceed_msg"] = "Your daily limit for using the AI feature is exceeded.";

$string['remaining_ai_attempt_count_text'] = '{$a->remaining} attempt(s) out of {$a->maximum} remaining.';

$string['view_submission'] = 'View Submission';
$string['not_available'] = 'N/A';

$string['word'] = 'Word';
$string['sentence'] = 'Sentence';
$string['paragraph'] = 'Paragraph';
$string['character'] = 'Character';
$string['expand_command'] = "Expand on the following";

// OpenAI API
$string['system_role_message'] = 'You are a helpful assistant.';

// Events
$string['eventassessableuploaded'] = 'Uploaded assessable';
$string['event_ai_text_generated'] = 'AI writer text generated';

// Errors
$string['error_course_module_not_found_by_assign_id'] = 'Could not find course module by assignment id {$a}';
$string['error_user_exceed_attempts'] = 'You have already exceeded the attempt limit';
$string['error_invalid_step_number'] = 'Invalid step number {$a}';
$string['error_overdue_assignment'] = 'The assignment is overdue';
$string['error_generate_ai_text_api'] = 'Could not generate AI text this time';
$string['error_expand_ai_text_api'] = 'Could not expand AI text this time';
$string['error_record_history_api'] = 'Could not record history this time';
