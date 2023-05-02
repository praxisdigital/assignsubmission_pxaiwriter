<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity as history_entity;
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
        return 'pxaiwriter_history';
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
        [$in_type_sql, $type_params] = $this->db()->get_in_or_equal([
            history_entity::TYPE_AI_GENERATE,
            history_entity::TYPE_AI_EXPAND,
        ], SQL_PARAMS_NAMED, 't');

        [$in_status_sql, $status_params] = $this->db()->get_in_or_equal([
            history_entity::STATUS_OK,
            history_entity::STATUS_DELETED,
        ], SQL_PARAMS_NAMED, 'st');

        $params = array_merge($type_params, $status_params);

        $params['userid'] = $user_id;
        $params['assignment'] = $assignment_id;
        $params['status'] = history_entity::STATUS_OK;
        $params['step'] = 1;
        $params['from_time'] = $from_time;
        $params['to_time'] = $to_time;

        $sql = "type $in_type_sql
        AND status $in_status_sql
        AND userid = :userid
        AND assignment = :assignment
        AND step = :step
        AND timecreated >= :from_time
        AND timecreated <= :to_time";

        try
        {
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
}
