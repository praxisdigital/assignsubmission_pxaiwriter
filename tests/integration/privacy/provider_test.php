<?php

namespace assignsubmission_pxaiwriter\integration\privacy;


use assign;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\collection as history_collection;
use assignsubmission_pxaiwriter\app\test\privacy_testcase;
use assignsubmission_pxaiwriter\privacy\provider;
use assignsubmission_pxaiwriter\task\delete_user_history;
use core_privacy\local\request\writer;
use mod_assign\privacy\assign_plugin_request_data;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class provider_test extends privacy_testcase
{
    private object $user;
    private object $course;
    private assign $assign;


    protected function setUp(): void
    {
        parent::setUp();
        $this->course = $this->create_course();
        $this->user = $this->create_user(['username' => 'user1']);
        $this->assign = $this->create_assignment_with_ai_writer($this->course);
        $this->enrol_user($this->user, $this->course);
        $this->setUser($this->user);
    }

    public function test_export_submission_user_data(): void
    {
        $input_text = 'This is a AI text';

        $submission = $this->create_submission($this->assign, $this->user);
        $steps_config = [];
        $steps_config[] = $this->get_step_data_with_ai_text($input_text);

        $this->save_submission(
            $this->assign,
            $submission,
            $this->get_ai_writer_form_data($steps_config)
        );

        $writer = writer::with_context($this->assign->get_context());
        self::assertFalse($writer->has_any_data());

        $request = new assign_plugin_request_data(
            $this->assign->get_context(),
            $this->assign,
            $submission,
            []
        );

        $request->set_userids([$this->user->id]);

        $request->populate_submissions_and_grades();

        provider::export_submission_user_data($request);

        $history = $this->factory()->ai()->history()->repository()->get_latest_by_submission($submission);
        $data = (array)$writer->get_data($request->get_subcontext());

        self::assertSame(
            $this->get_privacy_string("pxaiwriter_history:status:{$history->get_status()}"),
            $data['status']
        );

        self::assertSame(
            $this->get_privacy_string("pxaiwriter_history:type:{$history->get_type()}"),
            $data['type']
        );

        self::assertSame(
            $history->get_input_text(),
            $data['input_text']
        );

        self::assertSame(
            $history->get_data(),
            $data['data']
        );
    }

    public function test_delete_submission_for_userid(): void
    {
        $assign_id = (int)$this->assign->get_instance()->id;

        $input_text = 'A text for AI';
        $steps_config = [];
        $steps_config[] = $this->get_step_data_with_ai_text($input_text);

        $submission = $this->create_submission($this->assign, $this->user);
        $this->save_submission(
            $this->assign,
            $submission,
            $this->get_ai_writer_form_data($steps_config)
        );

        $history_list = $this->factory()->ai()->history()->repository()->get_all_by_user_assignment(
            $this->user->id,
            $assign_id
        );

        self::assertNotEmpty($history_list);

        $this->run_delete_user_history_task();

        $request = new assign_plugin_request_data(
            $this->assign->get_context(),
            $this->assign,
            $submission,
            []
        );
        provider::delete_submission_for_userid($request);

        $actual = $this->factory()->ai()->history()->repository()->get_all_by_user_assignment(
            $this->user->id,
            $assign_id
        );

        self::assertEmpty($actual);
    }

    public function test_delete_submission_for_context(): void
    {
        $assign2 = $this->create_assignment_with_ai_writer($this->course);

        $submission1 = $this->create_submission($this->assign, $this->user);
        $submission2 = $this->create_submission($assign2, $this->user);

        $this->save_submission(
            $this->assign,
            $submission1,
            $this->get_ai_writer_form_data(['value' => 'A text for AI'])
        );
        $this->save_submission(
            $assign2,
            $submission2,
            $this->get_ai_writer_form_data(['value' => 'A text for AI 2'])
        );

        $request = new assign_plugin_request_data(
            $this->assign->get_context(),
            $this->assign,
            $submission1,
            []
        );

        $history_list = $this->get_all_history_by_user_id($this->user->id);

        self::assertCount(2, $history_list);

        provider::delete_submission_for_context($request);

        $this->run_delete_user_history_task();

        $actual = $this->get_all_history_by_user_id($this->user->id);

        self::assertCount(1, $actual);
    }

    public function test_delete_submissions(): void
    {
        $user2 = $this->create_user(['username' => 'user2']);

        $this->enrol_user($user2, $this->course);

        $grouping = $this->create_grouping($this->course);
        $group = $this->create_group($this->course);

        $this->add_grouping_group($grouping, $group);
        $this->add_group_member($group, $this->user);
        $this->add_group_member($group, $user2);

        $assign2 = $this->create_assignment_with_ai_writer($this->course, 2, [
            'teamsubmission' => 1,
            'teamsubmissiongroupingid' => $grouping->id,
            'preventsubmissionnotingroup' => 0,
            'requireallteammemberssubmit' => 0
        ]);

        $submission1 = $this->create_submission($this->assign, $this->user);
        $this->save_submission(
            $this->assign,
            $submission1,
            $this->get_ai_writer_form_data(['value' => 'Submission 1 text'])
        );

        self::setUser($user2);
        $submission2 = $this->create_submission($this->assign, $user2);
        $submission3 = $this->create_submission_with_group($assign2, $user2, $group);
        $this->save_submission(
            $this->assign,
            $submission2,
            $this->get_ai_writer_form_data(['value' => 'Submission 2 text'])
        );
        $this->save_submission(
            $assign2,
            $submission3,
            $this->get_ai_writer_form_data(['value' => 'Submission 3 text'])
        );

        self::setUser($this->user);

        $request = new assign_plugin_request_data(
            $this->assign->get_context(),
            $this->assign,
            $submission1,
            []
        );

        $request->set_userids([$this->user->id]);

        $request->populate_submissions_and_grades();

        $history_list = $this->get_all_history_by_user_id($this->user->id);

        self::assertCount(2, $history_list);

        provider::delete_submissions($request);

        $actual = $this->get_all_history_by_user_id($this->user->id);

        self::assertCount(1, $actual);

        $history = $actual->current();

        self::assertEquals(
            $submission3->id,
            $history->get_submission()
        );
    }

    private function get_all_history_by_user_id(int $user_id): history_collection
    {
        $sql = "SELECT DISTINCT h.* FROM {pxaiwriter_history} h
                JOIN {assign_submission} s ON s.id = h.submission
                LEFT JOIN {groups_members} gm ON gm.groupid = s.groupid
                WHERE s.userid = ? OR gm.userid = ?";
        $records = $this->db()->get_recordset_sql($sql, [$user_id, $user_id]);
        $collection = $this->factory()->ai()->history()->mapper()->map_collection($records);
        $records->close();
        return $collection;
    }

    private function run_delete_user_history_task(): void
    {
        self::runAdhocTasks(delete_user_history::class);
    }

    private function get_privacy_string(string $key): string
    {
        return $this->factory()->moodle()->get_string(
            "privacy:metadata:{$key}",
        );
    }
}
