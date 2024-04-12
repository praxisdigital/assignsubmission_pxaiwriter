<?php

namespace assignsubmission_pxaiwriter\integration\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity as history_entity;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\repository as history_repository;
use assignsubmission_pxaiwriter\app\test\integration_testcase;
use mod_assign_testable_assign;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class repository_test extends integration_testcase
{
    private object $user1;
    private object $user2;
    private object $course;
    private mod_assign_testable_assign $assignment;
    private history_repository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user1 = $this->create_user();
        $this->user2 = $this->create_user();
        $this->course = $this->create_course();
        $this->assignment = $this->create_assignment_with_ai_writer($this->course);
        $this->enrol_user($this->user1, $this->course);
        $this->enrol_user($this->user2, $this->course);

        $this->repo = $this->factory()->ai()->history()->repository();
    }

    public function test_get_all_drafted_by_submission(): void
    {
        $submission = $this->create_submission($this->assignment, $this->user1);

        $archive = $this->factory()->ai()->history()->archive_generate_ai_text(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $user_text = 'Random text...';
        $history = $archive->commit($user_text, 1);

        self::assertSame(
            history_entity::STATUS_DRAFTED,
            $history->get_status()
        );

        $history_list = $this->repo->get_all_drafted_by_submission($submission->id);
        self::assertCount(1, $history_list);
        self::assertSame(
            $history->get_id(),
            $history_list[$history->get_id()]->get_id()
        );

        $archive->save_draft();

        $history_list = $this->repo->get_all_drafted_by_submission($submission->id);
        self::assertCount(0, $history_list);
    }

    public function test_get_all_submitted_by_submission(): void
    {
        $submission = $this->create_submission($this->assignment, $this->user1);

        $archive = $this->factory()->ai()->history()->archive_generate_ai_text(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $user_text = 'Random text...';
        $history = $archive->commit($user_text, 1);

        self::assertSame(
            history_entity::STATUS_DRAFTED,
            $history->get_status()
        );

        $history_list = $this->repo->get_all_submitted_by_submission($submission->id);
        self::assertCount(0, $history_list);

        $archive->save_draft();

        $history_list = $this->repo->get_all_submitted_by_submission($submission->id);
        self::assertCount(1, $history_list);

        self::assertSame(
            $history->get_id(),
            $history_list[$history->get_id()]->get_id()
        );
    }

    public function test_get_all_by_submission(): void
    {
        $user3 = $this->create_user();
        $this->enrol_user($user3, $this->course);

        $submission1 = $this->create_submission($this->assignment, $this->user1);
        $submission2 = $this->create_submission($this->assignment, $this->user2);
        $submission3 = $this->create_submission($this->assignment, $user3);

        $archive1 = $this->factory()->ai()->history()->archive_generate_ai_text(
            $submission1->assignment,
            $submission1->id,
            $submission1->userid
        );
        $archive2 = $this->factory()->ai()->history()->archive_generate_ai_text(
            $submission2->assignment,
            $submission2->id,
            $submission2->userid
        );

        $user_text = 'This is a user text';

        $history1 = $archive1->commit($user_text);
        $history2 = $archive2->commit($user_text);

        $history_list = $this->repo->get_all_by_submission($submission1->id);

        self::assertCount(1, $history_list);
        self::assertSame(
            $history1->get_id(),
            $history_list[$history1->get_id()]->get_id()
        );

        $history_list = $this->repo->get_all_by_submission($submission2->id);

        self::assertCount(1, $history_list);
        self::assertSame(
            $history2->get_id(),
            $history_list[$history2->get_id()]->get_id()
        );

        $history_list = $this->repo->get_all_by_submission($submission3->id);
        self::assertCount(0, $history_list);
    }

    public function test_get_history_by_hashcode(): void
    {
        self::setUser($this->user1);

        $submission = $this->create_submission($this->assignment, $this->user1);

        $user_text = 'This is a user text';
        $archive = $this->factory()->ai()->history()->archive_generate_ai_text(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $history = $archive->commit($user_text);

        $checksum = $this->factory()->helper()->hash()->sha256()->digest($user_text);

        $actual = $this->repo->get_by_hashcode(
            $this->user1->id,
            $this->assignment->get_instance()->id,
            $checksum,
            $history->get_step()
        );

        self::assertSame(
            $history->get_id(),
            $actual->get_id()
        );
        self::assertSame(
            $history->get_hashcode(),
            $actual->get_hashcode()
        );
    }
}
