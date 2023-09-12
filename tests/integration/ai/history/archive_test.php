<?php

namespace assignsubmission_pxaiwriter\integration\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity as history_entity;
use assignsubmission_pxaiwriter\app\factory;
use assignsubmission_pxaiwriter\app\test\integration_testcase;
use mod_assign_testable_assign;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class archive_test extends integration_testcase
{
    private object $user;
    private mod_assign_testable_assign $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->create_user();
        $course = $this->create_course();
        $this->assignment = $this->create_assignment_with_ai_writer($course);

        $this->enrol_user($this->user, $course);
    }

    public function test_commit_by_user(): void
    {
        $submission = $this->create_submission($this->assignment, $this->user);

        $archive = $this->factory()->ai()->history()->archive_user_edit(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $user_text = 'This is a user text';
        $archive->commit($user_text);

        $history_list = $this->factory()->ai()->history()->repository()->get_all_by_user_assignment(
            $this->user->id,
            $submission->assignment
        );

        self::assertCount(
            1,
            $history_list
        );

        $history = $history_list->current();
        self::assertSame(
            $user_text,
            $history->get_input_text()
        );
        self::assertSame(
            $user_text,
            $history->get_data()
        );
        self::assertSame(
            history_entity::TYPE_USER_EDIT,
            $history->get_type()
        );
    }

    public function test_commit_generate_ai_text_by_user(): void
    {
        $submission = $this->create_submission($this->assignment, $this->user);

        $archive = $this->factory()->ai()->history()->archive_user_edit(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $user_text = 'This is a user text';
        $ai_text = 'This is an AI text';
        $final_text = $user_text . $ai_text;
        $archive->commit_by_generate_ai_text(
            $user_text,
            $ai_text,
            $final_text,
            '{"message": "Well done!"}'
        );

        $history_list = $this->factory()->ai()->history()->repository()->get_all_by_user_assignment(
            $this->user->id,
            $submission->assignment
        );

        self::assertCount(
            1,
            $history_list
        );

        $history = $history_list->current();
        self::assertSame(
            $user_text,
            $history->get_input_text()
        );
        self::assertSame(
            $ai_text,
            $history->get_ai_text()
        );
        self::assertSame(
            $final_text,
            $history->get_data()
        );

        self::assertSame(
            history_entity::TYPE_AI_GENERATE,
            $history->get_type()
        );
    }

    public function test_commit_expand_ai_by_user(): void
    {
        $submission = $this->create_submission($this->assignment, $this->user);

        $archive = $this->factory()->ai()->history()->archive_user_edit(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $user_text = 'This is a user text';
        $ai_text = 'This is an AI text';
        $final_text = $user_text . $ai_text;
        $archive->commit_by_expand_ai_text(
            $user_text,
            $ai_text,
            $final_text,
            '{"message": "Well done!"}'
        );

        $history_list = $this->factory()->ai()->history()->repository()->get_all_by_user_assignment(
            $this->user->id,
            $submission->assignment
        );

        self::assertCount(
            1,
            $history_list
        );

        $history = $history_list->current();
        self::assertSame(
            $user_text,
            $history->get_input_text()
        );
        self::assertSame(
            $ai_text,
            $history->get_ai_text()
        );
        self::assertSame(
            $final_text,
            $history->get_data()
        );

        self::assertSame(
            history_entity::TYPE_AI_EXPAND,
            $history->get_type()
        );
    }

    public function test_commit_with_existed_input_text_should_not_be_record(): void
    {
        $submission = $this->create_submission($this->assignment, $this->user);

        $user_text = 'This is a user text';
        $archive = $this->factory()->ai()->history()->archive_generate_ai_text(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $history = $archive->commit($user_text);
        self::assertSame(
            $user_text,
            $history->get_data()
        );

        $history2 = $archive->commit($user_text);
        self::assertSame(
            $history->get_id(),
            $history2->get_id()
        );
    }
}
