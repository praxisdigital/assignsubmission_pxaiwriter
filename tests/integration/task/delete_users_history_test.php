<?php

namespace assignsubmission_pxaiwriter\integration\task;


use assignsubmission_pxaiwriter\app\test\integration_testcase;
use assignsubmission_pxaiwriter\task\delete_user_history;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class delete_users_history_test extends integration_testcase
{
    public function test_delete_history_by_id(): void
    {
        $deleted_history = $this->create_fake_history();
        $history = $this->create_fake_history();

        delete_user_history::schedule_by_history_ids([
            $deleted_history->id
        ]);
        $this->runAdhocTasks(delete_user_history::class);

        $records = $this->get_history_records();

        self::assertCount(1, $records);

        $actual = $records[$history->id];

        self::assertEquals(
            $history->id,
            $actual->id
        );
    }

    public function test_delete_history_by_assignment_id(): void
    {
        $deleted_assignment_id = 1;
        $assignment_id = 2;
        $deleted_data = ['assignment' => $deleted_assignment_id];
        $this->create_fake_history($deleted_data);
        $this->create_fake_history($deleted_data);
        $history = $this->create_fake_history(['assignment' => $assignment_id]);

        delete_user_history::schedule_by_assignment_id($deleted_assignment_id);
        $this->runAdhocTasks(delete_user_history::class);

        $records = $this->get_history_records();

        self::assertCount(1, $records);

        $actual = $records[$history->id];

        self::assertEquals(
            $history->id,
            $actual->id
        );
        self::assertEquals(
            $assignment_id,
            $actual->assignment
        );
    }

    public function test_delete_history_by_assignment_id_with_no_history(): void
    {
        $assignment_id = 1;

        delete_user_history::schedule_by_assignment_id($assignment_id);
        $this->runAdhocTasks(delete_user_history::class);

        $records = $this->get_history_records();

        self::assertCount(0, $records);
    }

    public function test_delete_history_by_submission_id(): void
    {
        $deleted_submission_id = 1;
        $submission_id = 2;

        $this->create_fake_history(['submission' => $deleted_submission_id]);
        $history = $this->create_fake_history(['submission' => $submission_id]);

        delete_user_history::schedule_by_submission_id($deleted_submission_id);
        $this->runAdhocTasks(delete_user_history::class);

        $records = $this->get_history_records();
        self::assertCount(1, $records);

        $actual = $records[$history->id];

        self::assertEquals(
            $history->id,
            $actual->id
        );
        self::assertEquals(
            $submission_id,
            $actual->submission
        );
    }

    public function test_delete_history_by_submission_id_with_no_history(): void
    {
        $submission_id = 1;

        delete_user_history::schedule_by_submission_id($submission_id);
        $this->runAdhocTasks(delete_user_history::class);

        $records = $this->get_history_records();

        self::assertCount(0, $records);
    }

    private function get_history_records(array $params = []): array
    {
        return $this->factory()->moodle()->db()->get_records('pxaiwriter_history', $params);
    }

    private function create_fake_history(array $record = []): object
    {
        $record['userid'] ??= 0;
        $record['assignment'] ??= 0;
        $record['submission'] ??= 0;
        $record['step'] ??= 1;
        $record['status'] ??= 'deleted';
        $record['type'] ??= 'user-edit';
        $record['input_text'] ??= '';
        $record['ai_text'] ??= '';
        $record['response'] ??= '';
        $record['hashcode'] ??= '';
        $record['timecreated'] ??= time();
        $record['timemodified'] ??= time();

        $data = (object)$record;
        $data->id = $this->factory()->moodle()->db()->insert_record('pxaiwriter_history', $data);
        return $data;
    }
}
