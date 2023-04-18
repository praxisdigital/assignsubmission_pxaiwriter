<?php

namespace assignsubmission_pxaiwriter\integration\ai\history;


use assignsubmission_pxaiwriter\app\ai\attempt\interfaces\entity as attempt_entity_interface;
use assignsubmission_pxaiwriter\app\ai\history\archive;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\archive as archive_interface;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\repository as history_repository_interface;
use assignsubmission_pxaiwriter\app\factory;
use assignsubmission_pxaiwriter\app\test\integration_testcase;
use assignsubmission_pxaiwriter\app\test\mock\ai\factory as mock_ai_factory;
use assignsubmission_pxaiwriter\app\test\mock\ai\history\factory as mock_history_factory;
use assignsubmission_pxaiwriter\app\test\mock\factory as mock_base_factory;
use Exception;
use mod_assign_testable_assign;
use moodle_transaction;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class archive_test extends integration_testcase
{
    private object $user;
    private object $course;
    private mod_assign_testable_assign $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->create_user();
        $this->course = $this->create_course();
        $this->assignment = $this->create_assignment_with_ai_writer($this->course);

        $this->enrol_user($this->user, $this->course);
    }


    public function test_start_attempt(): void
    {
        $assign_instance = $this->assignment->get_instance($this->user->id);
        $transaction = $this->db()->start_delegated_transaction();

        $archive = $this->get_archive($transaction);

        $text = 'This is an attempted text';
        $archive->start_attempt($text);
        $transaction->allow_commit();

        $attempts = $this->db()->get_records(
            'pxaiwriter_user_attempts',
            ['userid' => $this->user->id, 'assignment' => $assign_instance->id]
        );
        self::assertCount(1, $attempts);

        $attempt = reset($attempts);
        self::assertSame(
            $text,
            $attempt->data
        );
    }

    public function test_commit_history(): void
    {
        $assign_instance = $this->assignment->get_instance($this->user->id);

        $archive = $this->get_archive();

        $user_text = 'This is a user text';
        $ai_text = 'This is an AI text';
        $archive->start_attempt($user_text);

        $actual_history = $archive->commit($user_text, $ai_text);

        $attempts = $this->get_user_attempts($this->user->id, $assign_instance->id);
        self::assertCount(1, $attempts);

        $history_list = $this->get_user_history($this->user->id, $assign_instance->id);
        self::assertCount(1, $history_list);

        $user_attempt_record = reset($attempts);
        $user_history_record = reset($history_list);

        $user_attempt_entity = factory::make()->ai()->attempt()->mapper()->map($user_attempt_record);
        $user_history_entity = factory::make()->ai()->history()->mapper()->map($user_history_record);

        self::assertSame(
            $user_history_entity->get_id(),
            $actual_history->get_id()
        );

        self::assertSame(
            $user_text,
            $user_attempt_entity->get_data()
        );

        self::assertSame(
            $user_text,
            $user_history_entity->get_data()
        );

        self::assertSame(
            $ai_text,
            $user_history_entity->get_ai_text()
        );
    }

    public function test_record_history_when_user_provided_different_data_then_previous_record(): void
    {
        $archive = $this->get_archive();
        $user_text = 'This is a user text';
        $ai_text = 'This is an AI text';
        $archive->start_attempt($user_text);
        $first_history = $archive->commit($user_text, $ai_text);

        $archive = $this->get_archive();
        $new_user_text = 'This is a new user text';
        $new_ai_text = 'This is a new AI text';
        $archive->start_attempt($new_user_text);
        $latest_history = $archive->commit($new_ai_text, $new_ai_text);

        self::assertNotSame(
            $first_history->get_id(),
            $latest_history->get_id()
        );
        self::assertNotSame(
            $first_history->get_hashcode(),
            $latest_history->get_hashcode()
        );
    }

    public function test_skip_history_record_when_user_provided_the_same_data(): void
    {
        $archive = $this->get_archive();
        $user_text = 'This is a user text';
        $ai_text = 'This is an AI text';
        $archive->start_attempt($user_text);
        $first_history = $archive->commit($user_text, $ai_text);

        $archive = $this->get_archive();
        $archive->start_attempt($user_text);
        $latest_history = $archive->commit($user_text, $ai_text);

        self::assertSame(
            $first_history->get_id(),
            $latest_history->get_id()
        );
    }

    public function test_rollback_history_record(): void
    {
        $assign_instance = $this->assignment->get_instance($this->user->id);

        $error_message = 'Cannot insert history';

        $mock_history_repo = $this->createMock(history_repository_interface::class);
        $mock_history_repo->expects(self::once())
            ->method('insert')
            ->willReturnCallback(static function() use($error_message) {
                throw new Exception($error_message);
            });

        $history_factory = new mock_history_factory();
        $history_factory->set_mock_method('repository', $mock_history_repo);

        $ai_factory = new mock_ai_factory();
        $ai_factory->set_mock_method('history', $history_factory);

        $factory = new mock_base_factory();
        $factory->set_mock_method('ai', $ai_factory);

        $archive = new archive(
            $factory,
            $assign_instance->id,
            1,
            $this->user->id
        );
        $user_text = 'This is a user text';
        $ai_text = 'This is an AI text';

        try
        {
            $archive->start_attempt($user_text);
            $archive->commit($user_text, $ai_text);
        }
        catch (Exception $exception)
        {
            self::assertSame($error_message, $exception->getMessage());
            $archive->rollback($user_text, $exception);
        }

        $attempts = $this->get_user_attempts($this->user->id, $assign_instance->id);
        self::assertCount(1, $attempts);

        $attempt = factory::make()->ai()->attempt()->mapper()->map(reset($attempts));

        self::assertSame(
            attempt_entity_interface::STATUS_FAILED,
            $attempt->get_status()
        );
    }

    private function get_archive(?moodle_transaction $transaction = null): archive_interface
    {
        return factory::make()->ai()->history()->archive(
            $this->assignment->get_instance($this->user->id)->id,
            1,
            $this->user->id,
            $transaction
        );
    }

    private function get_user_attempts(int $user_id, int $assignment_id): array
    {
        return $this->db()->get_records(
            'pxaiwriter_user_attempts',
            ['userid' => $user_id, 'assignment' => $assignment_id]
        );
    }

    private function get_user_history(int $user_id, int $assignment_id): array
    {
        return $this->db()->get_records(
            'pxaiwriter_user_history',
            ['userid' => $user_id, 'assignment' => $assignment_id]
        );
    }
}
