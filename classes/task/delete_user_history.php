<?php

namespace assignsubmission_pxaiwriter\task;


use assignsubmission_pxaiwriter\app\factory;
use core\task\adhoc_task;
use core\task\manager;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class delete_user_history extends adhoc_task
{
    public function execute()
    {
        $ids = $this->get_history_ids();
        if (empty($ids))
        {
            return;
        }

        factory::make()->moodle()->db()->delete_records_list(
            'pxaiwriter_history',
            'id',
            $ids
        );
    }

    private function get_history_ids(): array
    {
        $data = (array)$this->get_custom_data();
        return $data['ids'] ?? [];
    }

    public static function schedule_by_history_ids(array $ids): self
    {
        $task = new self();
        $task->set_custom_data(['ids' => $ids]);
        manager::queue_adhoc_task($task, true);
        return $task;
    }

    public static function schedule_by_assignment_id(int $assignment_id): self
    {
        $ids = self::get_history_ids_by_sql_select(
            'assignment = :assignment_id',
            ['assignment_id' => $assignment_id]
        );
        return self::schedule_by_history_ids($ids);
    }

    public static function schedule_by_submission_id(int $submission_id): self
    {
        $ids = self::get_history_ids_by_sql_select(
            'submission = :submission_id',
            ['submission_id' => $submission_id]
        );
        return self::schedule_by_history_ids($ids);
    }

    private static function get_history_ids_by_sql_select(string $select, array $params = []): array
    {
        return factory::make()->moodle()->db()->get_fieldset_select(
            'pxaiwriter_history',
            'id',
            $select,
            $params
        );
    }
}
