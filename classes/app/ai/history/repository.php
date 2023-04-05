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

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    protected function get_table(): string
    {
        return 'pxaiwriter_user_history';
    }

    private function db(): moodle_database
    {
        return $this->factory->moodle()->db();
    }

    public function get_by_hashcode(int $user_id, int $assignment_id, string $hashcode, int $step = 1): ?entity
    {
        try
        {
            $record = $this->db()->get_record($this->get_table(), [
                'userid' => $user_id,
                'assignment' => $assignment_id,
                'hashcode' => $hashcode
            ], '*', MUST_EXIST);
            return $this->factory->ai()->history()->mapper()->map($record);
        }
        catch (dml_exception $exception)
        {
        }

        return null;
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
            ]);
            $collection = $this->factory->ai()->history()->mapper()->map_collection($records);
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
}
