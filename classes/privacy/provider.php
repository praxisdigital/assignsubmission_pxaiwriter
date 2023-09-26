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

use assignsubmission_pxaiwriter\app\factory;
use \core_privacy\local\metadata\collection;
use core_privacy\local\request\userlist;
use \core_privacy\local\request\contextlist;
use \mod_assign\privacy\assign_plugin_request_data;
use mod_assign\privacy\useridlist;

/**
 * Privacy class for requesting user data.
 *
 * @package    assignsubmission_pxaiwriter
 * @copyright  2023 Moxis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
            'input_text' => 'privacy:metadata:pxaiwriter_history:input_text',
            'data' => 'privacy:metadata:pxaiwriter_history:data',
        ]);

        $collection->link_subsystem('core_files', 'privacy:metadata:file');

        return $collection;
    }

    public static function get_context_for_userid_within_submission(int $userid, contextlist $contextlist)
    {
        $sql = "SELECT contextid
                  FROM {pxaiwriter_history}
                 WHERE userid = :userid";
    }

    public static function get_student_user_ids(useridlist $useridlist)
    {
        //$useridlist->add_from_sql();
    }

    public static function export_submission_user_data(assign_plugin_request_data $exportdata)
    {
        // TODO: Implement export_submission_user_data() method.
    }

    public static function delete_submission_for_context(assign_plugin_request_data $requestdata)
    {

    }

    public static function delete_submission_for_userid(assign_plugin_request_data $exportdata)
    {
        $user_ids = $exportdata->get_userids();
        $submission_ids = $exportdata->get_submissionids();
        $ids = self::get_history_ids_by_submissions_and_users($submission_ids, $user_ids);

        if (empty($ids))
        {
            return;
        }

        factory::make()->moodle()->db()->delete_records_list('pxaiwriter_history', 'id', $ids);
    }

    public static function get_userids_from_context(userlist $userlist)
    {
        $context = $userlist->get_context();
        if ($context->contextlevel === CONTEXT_MODULE)
        {
            
        }
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

    /**
     * @param int[] $submission_ids
     * @param int[] $user_ids
     * @return int[]
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private static function get_history_ids_by_submissions_and_users(array $submission_ids, array $user_ids): array
    {
        $db = factory::make()->moodle()->db();

        [$in_submission_sql, $submission_params] = $db->get_in_or_equal($submission_ids);
        [$in_user_sql, $user_params] = $db->get_in_or_equal($user_ids);

        $params = array_merge($submission_params, $user_params);

        $items = $db->get_fieldset_select(
            'pxaiwriter_history',
            'id',
            "submission {$in_submission_sql} AND userid {$in_user_sql}",
            $params
        );

        $ids = [];
        foreach ($items as $id)
        {
            $ids[$id] = $id;
        }
        return $ids;
    }

//    /**
//     * Return meta data about this plugin.
//     *
//     * @param  collection $collection A list of information to add to.
//     * @return collection Return the collection after adding to it.
//     */
//    public static function get_metadata(collection $collection) : collection {
//        $detail = [
//                    'assignment' => 'privacy:metadata:assignmentid',
//                    'submission' => 'privacy:metadata:submissionpurpose',
//                    'steps_data' => 'privacy:metadata:textpurpose'
//                  ];
//        $collection->add_database_table('assignsubmission_pxaiwriter', $detail, 'privacy:metadata:tablepurpose');
//        $collection->link_subsystem('core_files', 'privacy:metadata:filepurpose');
//        return $collection;
//    }
//
//    /**
//     * This is covered by mod_assign provider and the query on assign_submissions.
//     *
//     * @param  int $userid The user ID that we are finding contexts for.
//     * @param  contextlist $contextlist A context list to add sql and params to for contexts.
//     */
//    public static function get_context_for_userid_within_submission(int $userid, contextlist $contextlist) {
//        // This is already fetched from mod_assign.
//    }
//
//    /**
//     * This is also covered by the mod_assign provider and it's queries.
//     *
//     * @param  \mod_assign\privacy\useridlist $useridlist An object for obtaining user IDs of students.
//     */
//    public static function get_student_user_ids(\mod_assign\privacy\useridlist $useridlist) {
//        // No need.
//    }
//
//    /**
//     * If you have tables that contain userids and you can generate entries in your tables without creating an
//     * entry in the assign_submission table then please fill in this method.
//     *
//     * @param  \core_privacy\local\request\userlist $userlist The userlist object
//     */
//    public static function get_userids_from_context(\core_privacy\local\request\userlist $userlist) {
//        // Not required.
//    }
//
//    /**
//     * Export all user data for this plugin.
//     *
//     * @param  assign_plugin_request_data $exportdata Data used to determine which context and user to export and other useful
//     * information to help with exporting.
//     */
//    public static function export_submission_user_data(assign_plugin_request_data $exportdata) {
//        // We currently don't show submissions to teachers when exporting their data.
//        if ($exportdata->get_user() != null) {
//            return null;
//        }
//        // Retrieve text for this submission.
//        $assign = $exportdata->get_assign();
//        $plugin = $assign->get_plugin_by_type('assignsubmission', 'pxaiwriter');
//        $submission = $exportdata->get_pluginobject();
//        $stepsdata = $plugin->get_editor_text('pxaiwriter', $submission->id);
//        $context = $exportdata->get_context();
//        if (!empty($stepsdata)) {
//            $submissiontext = new \stdClass();
//            $currentpath = $exportdata->get_subcontext();
//            $currentpath[] = get_string('privacy:path', 'assignsubmission_pxaiwriter');
//            $submissiontext->text = writer::with_context($context)->rewrite_pluginfile_urls($currentpath,
//                    'assignsubmission_pxaiwriter', 'submissions_pxaiwriter', $submission->id, $stepsdata);
//            writer::with_context($context)
//                    ->export_area_files($currentpath, 'assignsubmission_pxaiwriter', 'submissions_pxaiwriter', $submission->id)
//                    // Add the text to the exporter.
//                    ->export_data($currentpath, $submissiontext);
//
//            // Handle plagiarism data.
//            $coursecontext = $context->get_course_context();
//            $userid = $submission->userid;
//            \core_plagiarism\privacy\provider::export_plagiarism_user_data($userid, $context, $currentpath, [
//                'cmid' => $context->instanceid,
//                'course' => $coursecontext->instanceid,
//                'userid' => $userid,
//                'content' => $stepsdata,
//                'assignment' => $submission->assignment
//            ]);
//        }
//    }
//
//    /**
//     * Any call to this method should delete all user data for the context defined in the deletion_criteria.
//     *
//     * @param  assign_plugin_request_data $requestdata Data useful for deleting user data from this sub-plugin.
//     */
//    public static function delete_submission_for_context(assign_plugin_request_data $requestdata) {
//        global $DB;
//
//        \core_plagiarism\privacy\provider::delete_plagiarism_for_context($requestdata->get_context());
//
//        // Delete related files.
//        $fs = get_file_storage();
//        $fs->delete_area_files($requestdata->get_context()->id, 'assignsubmission_pxaiwriter',
//        ASSIGNSUBMISSION_PXAIWRITER_FILEAREA);
//
//        // Delete the records in the table.
//        $DB->delete_records('assignsubmission_pxaiwriter', ['assignment' => $requestdata->get_assignid()]);
//    }
//
//    /**
//     * A call to this method should delete user data (where practicle) from the userid and context.
//     *
//     * @param  assign_plugin_request_data $deletedata Details about the user and context to focus the deletion.
//     */
//    public static function delete_submission_for_userid(assign_plugin_request_data $deletedata) {
//        global $DB;
//
//        \core_plagiarism\privacy\provider::delete_plagiarism_for_user($deletedata->get_user()->id, $deletedata->get_context());
//
//        $submissionid = $deletedata->get_pluginobject()->id;
//
//        // Delete related files.
//        $fs = get_file_storage();
//        $fs->delete_area_files($deletedata->get_context()->id, 'assignsubmission_pxaiwriter', ASSIGNSUBMISSION_PXAIWRITER_FILEAREA,
//                $submissionid);
//
//        // Delete the records in the table.
//        $DB->delete_records('assignsubmission_pxaiwriter', ['assignment' => $deletedata->get_assignid(),
//                'submission' => $submissionid]);
//    }
//
//    /**
//     * Deletes all submissions for the submission ids / userids provided in a context.
//     * assign_plugin_request_data contains:
//     * - context
//     * - assign object
//     * - submission ids (pluginids)
//     * - user ids
//     * @param  assign_plugin_request_data $deletedata A class that contains the relevant information required for deletion.
//     */
//    public static function delete_submissions(assign_plugin_request_data $deletedata) {
//        global $DB;
//
//        \core_plagiarism\privacy\provider::delete_plagiarism_for_users($deletedata->get_userids(), $deletedata->get_context());
//        if (empty($deletedata->get_submissionids())) {
//            return;
//        }
//
//        $fs = get_file_storage();
//        list($sql, $params) = $DB->get_in_or_equal($deletedata->get_submissionids(), SQL_PARAMS_NAMED);
//        $fs->delete_area_files_select($deletedata->get_context()->id,
//                'assignsubmission_pxaiwriter', ASSIGNSUBMISSION_PXAIWRITER_FILEAREA, $sql, $params);
//
//        $params['assignid'] = $deletedata->get_assignid();
//        $DB->delete_records_select('assignsubmission_pxaiwriter', "assignment = :assignid AND submission $sql", $params);
//    }
}
