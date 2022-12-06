<?php
$settings->add(new admin_setting_configcheckbox(
    'assignsubmission_aiwriter/default',
    new lang_string('default', 'assignsubmission_aiwriter'),
    new lang_string('default_help', 'assignsubmission_aiwriter'),
    1
));

if (isset($CFG->maxbytes)) {

    $name = new lang_string('maximumsubmissionsize', 'assignsubmission_aiwriter');
    $description = new lang_string('configmaxbytes', 'assignsubmission_aiwriter');

    $element = new admin_setting_configselect(
        'assignsubmission_aiwriter/maxbytes',
        $name,
        $description,
        1048576,
        get_max_upload_sizes($CFG->maxbytes)
    );
    $settings->add($element);
}
