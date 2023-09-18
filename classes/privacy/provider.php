<?php
/**
 * Privacy class for requesting user data.
 *
 * @package    assignsubmission_pxaiwriter
 * @copyright  2023 Moxis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_pxaiwriter\privacy;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');

use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
use assignsubmission_pxaiwriter\app\factory;
use assignsubmission_pxaiwriter\task\delete_user_history;
use \core_privacy\local\metadata\collection;
use core_privacy\local\request\content_writer;
use core_privacy\local\request\userlist;
use \core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use \mod_assign\privacy\assign_plugin_request_data;
use mod_assign\privacy\useridlist;


class provider implements
        \core_privacy\local\metadata\provider,
        \mod_assign\privacy\assignsubmission_provider,
        \mod_assign\privacy\assignsubmission_user_provider {

    public static function get_metadata(collection $collection): collection
    {
        $collection->add_database_table('assignsubmission_pxaiwriter', [
            'assignment' => 'privacy:metadata:assignsubmission_pxaiwriter:assignment',
            'submission' => 'privacy:metadata:assignsubmission_pxaiwriter:submission',
            'steps_data' => 'privacy:metadata:assignsubmission_pxaiwriter:steps_data'
        ], 'privacy:metadata:assignsubmission_pxaiwriter');

        $collection->add_database_table('pxaiwriter_history', [
            'assignment' => 'privacy:metadata:pxaiwriter_history:assignment',
            'submission' => 'privacy:metadata:pxaiwriter_history:submission',
            'step' => 'privacy:metadata:pxaiwriter_history:step',
            'status' => 'privacy:metadata:pxaiwriter_history:status',
            'type' => 'privacy:metadata:pxaiwriter_history:type',
            'data' => 'privacy:metadata:pxaiwriter_history:data',
        ]);

        $collection->link_subsystem('core_files', 'privacy:metadata:file');

        return $collection;
    }

    public static function get_context_for_userid_within_submission(int $userid, contextlist $contextlist)
    {
        $sql = "SELECT DISTINCT cx.id FROM {pxaiwriter_history} h
                JOIN {course_modules} cm ON cm.instance = h.assignment
                    AND cm.deletioninprogress = 0
                JOIN {modules} m ON m.id = cm.module
                JOIN {context} cx ON cx.instanceid = cm.id
                JOIN {assign_submission} s ON s.id = h.submission
                LEFT JOIN {groups_members} gm ON gm.groupid = s.groupid
                WHERE m.name = ?
                    AND cx.contextlevel = ?
                    AND (h.userid = ? OR gm.userid = ?)";

        $contextlist->add_from_sql($sql, [
            'assign',
            CONTEXT_MODULE,
            $userid,
            $userid
        ]);
    }

    public static function get_student_user_ids(useridlist $useridlist)
    {
    }

    public static function export_submission_user_data(assign_plugin_request_data $exportdata)
    {
        if ($exportdata->get_user() !== null)
        {
            return null;
        }

        $writer = writer::with_context($exportdata->get_context());
        $data_path = $exportdata->get_subcontext();
        $data_path[] = self::get_privacy_string('path');

        $data = self::get_history_data_by_submission($exportdata->get_pluginobject());

        $history_list = factory::make()->ai()->history()->repository()->get_all_by_assign_submission(
            $exportdata->get_assignid(),
            $exportdata->get_submissionids()
        );
        $data = self::get_history_list($data, $history_list);

        $writer->export_data($data_path, (object)$data);
    }

    public static function delete_submission_for_context(assign_plugin_request_data $requestdata)
    {
        $repo = factory::make()->file()->repository();
        $submission_ids = $requestdata->get_submissionids();


        foreach ($submission_ids as $id)
        {
            $repo->delete_files_by_submission(
                $requestdata->get_context(),
                $id
            );
        }

        $assign_id = $requestdata->get_assignid();
        delete_user_history::schedule_by_assignment_id($assign_id);
    }

    public static function delete_submission_for_userid(assign_plugin_request_data $exportdata)
    {
        $assign_id = $exportdata->get_assignid();
        $instance = $exportdata->get_pluginobject();

        if (!isset($instance->id))
        {
            return;
        }

        factory::make()->file()->repository()->delete_files_by_submission(
            $exportdata->get_context(),
            $instance->id
        );

        factory::make()->submission()->repository()->delete_by_assign_submission(
            $assign_id,
            $instance->id
        );
    }

    public static function get_userids_from_context(userlist $userlist)
    {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_MODULE)
        {
            return;
        }

        $sql = "SELECT userid FROM {pxaiwriter_history} WHERE assignment = :assignment";
        $userlist->add_from_sql('userid', $sql, ['assignment' => $context->instanceid]);
    }

    public static function delete_submissions(assign_plugin_request_data $deletedata)
    {
        $submission_ids = $deletedata->get_submissionids();
        factory::make()->moodle()->db()->delete_records_list(
            'pxaiwriter_history',
            'submission',
            $submission_ids
        );
    }

    private static function get_history_data(entity $history): object
    {
        return (object)[
            'step' => $history->get_step(),
            'status' => self::get_status_string($history->get_status()),
            'type' => self::get_type_string($history->get_type()),
            'data' => $history->get_data()
        ];
    }

    private static function get_status_string(string $status): string
    {
        switch ($status)
        {
            case entity::STATUS_DELETED:
            case entity::STATUS_DRAFTED:
            case entity::STATUS_FAILED:
            case entity::STATUS_SUBMITTED:
                return self::get_privacy_meta_string("pxaiwriter_history:status:{$status}");
        }
        return self::get_privacy_meta_string('pxaiwriter_history:status:unknown');
    }

    private static function get_history_data_by_submission(
        ?object $submission
    ): array
    {
        if (!$submission)
        {
            return [];
        }
        $history_list = factory::make()->ai()->history()->repository()->get_all_by_submission($submission->id);
        return self::get_history_list([], $history_list);
    }

    /**
     * @param array $data
     * @param iterable|entity[] $history_list
     * @return array
     */
    private static function get_history_list(array $data, iterable $history_list): array
    {
        foreach ($history_list as $history)
        {
            $data[$history->get_status()][] = self::get_history_data($history);
        }
        return $data;
    }

    private static function get_type_string(string $type): string
    {
        switch ($type)
        {
            case entity::TYPE_AI_EXPAND:
            case entity::TYPE_AI_GENERATE:
            case entity::TYPE_USER_EDIT:
                return self::get_privacy_meta_string("pxaiwriter_history:type:{$type}");
        }
        return self::get_privacy_meta_string('pxaiwriter_history:type:unknown');
    }

    private static function get_privacy_meta_string(string $identifier, $arguments = null): string
    {
        return self::get_privacy_string("metadata:$identifier", $arguments);
    }

    private static function get_privacy_string(string $identifier, $arguments = null): string
    {
        return factory::make()->moodle()->get_string(
            "privacy:{$identifier}",
            $arguments
        );
    }
}
