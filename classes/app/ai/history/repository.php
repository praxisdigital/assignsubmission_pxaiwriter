<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
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
    private interfaces\mapper $mapper;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
        $this->mapper = $this->factory->ai()->history()->mapper();
    }

    protected function get_table(): string
    {
        return 'pxaiwriter_history';
    }

    private function db(): moodle_database
    {
        return $this->factory->moodle()->db();
    }

    public function count_ai_generate_text_attempts(int $user_id, int $assignment_id, int $from_time, int $to_time): int
    {
        [$in_type_sql, $type_params] = $this->db()->get_in_or_equal([
            entity::TYPE_AI_GENERATE,
            entity::TYPE_AI_EXPAND,
        ], SQL_PARAMS_NAMED, 't');

        [$in_status_sql, $status_params] = $this->db()->get_in_or_equal([
            entity::STATUS_OK,
            entity::STATUS_DELETED,
        ], SQL_PARAMS_NAMED, 'st');


        $params = array_merge($type_params, $status_params);

        $params['userid'] = $user_id;
        $params['assignment'] = $assignment_id;
        $params['status'] = entity::STATUS_OK;
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
                self::TABLE,
                $sql,
                $params
            );
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_get_recordset($exception->getMessage(), $exception);
        }
    }

    public function count_by_user_submission(int $user_id, int $assignment_id, int $submission_id, ?int $step = null): int
    {
        try
        {
            $params = [
                'userid' => $user_id,
                'assignment' => $assignment_id,
                'submission' => $submission_id,
                'status' => entity::STATUS_OK,
            ];

            if ($step !== null)
            {
                $params['step'] = $step;
            }

            return $this->db()->count_records($this->get_table(), $params);
        }
        catch (\Exception $exception) {}
        return 0;
    }

    public function get_by_hashcode(int $user_id, int $assignment_id, string $hashcode, int $step = 1): ?entity
    {
        return $this->get_last_record_by_conditions([
            'userid' => $user_id,
            'assignment' => $assignment_id,
            'step' => $step,
            'hashcode' => $hashcode
        ], 'id DESC');
    }

    public function get_last_by_ids(array $ids): ?entity
    {
        if (empty($ids))
        {
            return null;
        }

        try
        {
            [$in_sql, $params] = $this->db()->get_in_or_equal($ids);
            $records = $this->db()->get_records_select(
                $this->get_table(),
                "id $in_sql",
                $params,
                'step DESC, id DESC',
                '*',
                0,
                1
            );
            return $this->get_last_item($records);
        }
        catch (dml_exception $exception) {}
        return null;
    }

    public function get_latest_by_submission(object $submission): ?entity
    {
        return $this->get_last_record_by_conditions([
            'submission' => $submission->id,
            'status' => entity::STATUS_OK
        ], 'step DESC, id DESC');
    }

    public function get_all_by_user_assignment(
        int $user_id,
        int $assignment_id,
        int $offset = 0,
        int $limit = 0
    ): interfaces\collection
    {
        try
        {
            $records = $this->db()->get_recordset($this->get_table(), [
                'userid' => $user_id,
                'assignment' => $assignment_id,
                'status' => entity::STATUS_OK
            ], 'step,id');
            $collection = $this->mapper->map_collection($records);
            $records->close();
            return $collection;
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_get_recordset(
                $exception->getMessage(),
                $exception
            );
        }
    }

    public function get_all_by_ids(array $ids): collection
    {
        if (empty($ids))
        {
            return $this->mapper->map_collection([]);
        }

        try
        {
            [$in_sql, $params] = $this->db()->get_in_or_equal($ids);
            $records = $this->db()->get_recordset_select(
                $this->get_table(),
                "id $in_sql",
                $params,
                'step, id'
            );
            $collection = $this->mapper->map_collection($records);
            $records->close();
            return $collection;
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
            $id = $this->db()->insert_record(
                $this->get_table(),
                $entity->to_object()
            );
            $entity->set_id($id);
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_insert(
                $exception->getMessage(),
                $exception
            );
        }
    }

    public function delete_by_id(int $id): void
    {
        try
        {
            $this->db()->delete_records(
                $this->get_table(),
                ['id' => $id]
            );
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_delete_records(
                $exception->getMessage(),
                $exception
            );
        }
    }

    public function delete_by_user_id(int $user_id): void
    {
        try
        {
            $this->db()->delete_records(
                $this->get_table(),
                ['userid' => $user_id]
            );
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_delete_records(
                $exception->getMessage(),
                $exception
            );
        }
    }

    public function delete_by_user_assignment(int $user_id, int $assignment_id): void
    {
        try
        {
            $records = $this->db()->get_recordset($this->get_table(), [
                'userid' => $user_id,
                'assignment' => $assignment_id
            ]);

            foreach ($records as $record)
            {
                $this->db()->update_record($this->get_table(), (object)[
                    'id' => $record->id,
                    'status' => entity::STATUS_DELETED
                ]);
            }

            $records->close();
        }
        catch (dml_exception $exception)
        {
            throw database_error_exception::by_delete_records(
                $exception->getMessage(),
                $exception
            );
        }
    }

    private function get_last_record_by_conditions(array $conditions, string $sort = ''): ?entity
    {
        try
        {
            $records = $this->db()->get_records(
                $this->get_table(),
                $conditions,
                $sort,
                '*',
                0,
                1
            );
            return $this->get_last_item($records);
        }
        catch (dml_exception $exception) {}

        return null;
    }

    private function get_last_item(array $items): ?entity
    {
        if (empty($items))
        {
            return null;
        }
        $last_index = array_key_last($items);
        $record = $items[$last_index];
        return $this->mapper->map($record);
    }
}
