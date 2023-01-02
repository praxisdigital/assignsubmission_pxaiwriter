<?php
$functions = array(
    'mod_mod_assign_submission_pxaiwriter_doaimagic' => array(
        'classname'   => 'mod_assign_submission_pxaiwriter_external',
        'methodname'  => 'do_ai_magic',
        'classpath'   => 'mod/assign/submission/pxaiwriter/externallib.php',
        'description' => 'Do the AI magic',
        'type'        => 'write',
        'ajax'        => true
    ),
    'mod_mod_assign_submission_pxaiwriter_expand' => array(
        'classname'   => 'mod_assign_submission_pxaiwriter_external',
        'methodname'  => 'expand',
        'classpath'   => 'mod/assign/submission/pxaiwriter/externallib.php',
        'description' => 'Do the AI magic expand',
        'type'        => 'write',
        'ajax'        => true
    )
);

$services = array(
    'PXAI Writer API' => array(
        'functions' => array(
            'mod_mod_assign_submission_pxaiwriter_doaimagic',
            'mod_mod_assign_submission_pxaiwriter_expand'
        ),
        'restrictedusers' => 0, // if 1, the administrator must manually select which user can use this service. 
        // (Administration > Plugins > Web services > Manage services > Authorised users)
        'enabled' => 1, // if 0, then token linked to this service won't work
        'shortname' => 'pxaiwriterapi' //the short name used to refer to this service from elsewhere including when fetching a token
    )
);
