<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt;


use assignsubmission_pxaiwriter\app\ai\attempt\interfaces\entity;
use assignsubmission_pxaiwriter\app\exceptions\database_error_exception;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use dml_exception;
use moodle_database;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class repository implements interfaces\repository
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    private function get_table(): string
    {
        return 'pxaiwriter_user_attempts';
    }

    private function db(): moodle_database
    {
        return $this->factory->moodle()->db();
    }

    public function get_remaining_attempt(
        int $user_id,
        int $assignment_id,
        int $from_time,
        int $to_time,
        ?int $max_attempts = null
    ): interfaces\data
    {
        $attempts_of_span = $this->count_success_attempt_in_timespan(
            $user_id,
            $assignment_id,
            $from_time,
            $to_time
        );

        return new data(
            $this->factory,
            $attempts_of_span,
            $max_attempts ?? $this->factory->setting()->admin()->get_attempt_count()
        );
    }

    public function get_today_remaining_attempt(int $user_id, int $assignment_id, ?int $max_attempts = null): interfaces\data
    {
        $today = $this->factory->helper()->times()->day();
        return $this->get_remaining_attempt(
            $user_id,
            $assignment_id,
            $today->get_start_of_day()->getTimestamp(),
            $today->get_end_of_day()->getTimestamp(),
            $max_attempts
        );
    }

    public function count_success_attempt_in_timespan(
        int $user_id,
        int $assignment_id,
        int $from_time,
        int $to_time
    ): int
    {
        try
        {
            $params = [
                'userid' => $user_id,
                'assignment' => $assignment_id,
                'status' => interfaces\entity::STATUS_OK,
                'from_time' => $from_time,
                'to_time' => $to_time,
            ];

            $sql = 'userid = :userid
            AND assignment = :assignment
            AND status = :status
            AND timecreated >= :from_time
            AND timecreated <= :to_time';

            return $this->db()->count_records_select(
                $this->get_table(),
                $sql,
                $params
            );
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_get_recordset(
                $exception->getMessage(),
                $exception
            );
        }
    }

    public function insert(entity $entity): void
    {
        try
        {
            $record = $entity->to_object();
            $id = $this->db()->insert_record($this->get_table(), $record);
            $entity->set_id($id);
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_insert($exception->getMessage(), $exception);
        }
    }

    public function delete(entity $entity): void
    {
        $this->delete_by_id($entity->get_id());
    }

    public function delete_by_id(string $id): void
    {
        $this->delete_attempts([
            'id' => $id
        ]);
    }

    public function delete_by_user_id(string $user_id): void
    {
        $this->delete_attempts([
            'userid' => $user_id
        ]);
    }

    public function delete_by_assignment_id(string $assignment_id): void
    {
        $this->delete_attempts([
            'assignment' => $assignment_id
        ]);
    }

    private function delete_attempts(array $conditions): void
    {
        try
        {
            $this->db()->delete_records(
                $this->get_table(),
                $conditions
            );
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_delete_records($exception->getMessage(), $exception);
        }
    }
}
