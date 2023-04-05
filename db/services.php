<?php

use assignsubmission_pxaiwriter\external\ai\expand_ai_text;
use assignsubmission_pxaiwriter\external\ai\generate_ai_text;
use assignsubmission_pxaiwriter\external\ai\record_history;

$functions = [
    'assignsubmission_pxaiwriter_generate_ai_text' => [
        'classname'   => generate_ai_text::class,
        'methodname'  => 'execute',
        'description' => 'Generate AI text',
        'type'        => 'write',
        'ajax'        => true
    ],
    'assignsubmission_pxaiwriter_expand_ai_text' => [
        'classname'   => expand_ai_text::class,
        'methodname'  => 'execute',
        'description' => 'Expand the AI text',
        'type'        => 'write',
        'ajax'        => true
    ],
    'assignsubmission_pxaiwriter_record_history' => [
        'classname'   => record_history::class,
        'methodname'  => 'execute',
        'description' => 'Record user history',
        'type'        => 'write',
        'ajax'        => true
    ],
];
