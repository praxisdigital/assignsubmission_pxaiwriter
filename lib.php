<?php

defined('MOODLE_INTERNAL') || die();

ensure_assignsubmission_pxaiwriter_features_are_setup();

function ensure_assignsubmission_pxaiwriter_features_are_setup(): void {
    $component = 'assignsubmission_pxaiwriter';

    // Obtain the factory instance.
    $factory = \local_mxaimanager\app\factory::make();

    // Create career transition feature if it does not exist.
    $name_identifier = 'ai:feature:assist_with_assignment_writing';

    try {
        // Register the feature in the AI Manager.
        $feature = $factory->ai()->feature()->entity()
            ->set_component($component)
            ->set_name_identifier($name_identifier)
            ->set_description_identifier("{$name_identifier}_desc");
        $feature->set_id(
            $factory->ai()->feature()->repository()->insert($feature)
        );

        // Register the actions for the feature.
        $factory->ai()->feature()->action()->repository()->insert(
            $factory->ai()->feature()->action()->entity()
                ->set_action_interface(\local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion::class)
                ->set_feature_id($feature->get_id())
        );
    } catch (\Exception) {
        // Ignore if the feature already exists.
    }
}
function assignsubmission_pxaiwriter_pluginfile(
    $course,
    $cm,
    context $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = array()
): bool {
    global $CFG;

    $moodle_factory = \assignsubmission_pxaiwriter\app\factory::make()->moodle();
    $db = $moodle_factory->db();

    if ($context->contextlevel != CONTEXT_MODULE)
    {
        return false;
    }

    require_login($course, false, $cm);

    $itemid = (int)array_shift($args);
    $record = $db->get_record(
        'assign_submission',
        ['id' => $itemid],
        'userid, assignment, groupid',
        MUST_EXIST
    );
    $userid = $record->userid;
    $groupid = $record->groupid;

    require_once $CFG->dirroot . '/mod/assign/locallib.php';

    $assign = new assign($context, $cm, $course);

    if ($assign->get_instance()->id != $record->assignment)
    {
        return false;
    }

    if ($assign->get_instance()->teamsubmission &&
        !$assign->can_view_group_submission($groupid))
    {
        return false;
    }

    if (!$assign->get_instance()->teamsubmission &&
        !$assign->can_view_submission($userid))
    {
        return false;
    }

    $relative = implode('/', $args);

    $fullpath = "/{$context->id}/assignsubmission_pxaiwriter/$filearea/$itemid/$relative";

    $storage = get_file_storage();
    $file = $storage->get_file_by_hash(sha1($fullpath));
    if (!$file || $file->is_directory())
    {
        return false;
    }

    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true, $options);

    return true;
}
