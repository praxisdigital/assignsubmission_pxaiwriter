<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class database_error_exception extends core_exception
{
    public static function by_delete_records(
        string $message,
        \Exception $inner_exception
    ): self
    {
        return new static(
            $message,
            0,
            $inner_exception
        );
    }

    public static function by_insert(
        string $message,
        \Exception $inner_exception
    ): self
    {
        return new static(
            $message,
            0,
            $inner_exception
        );
    }

    public static function by_get_recordset(
        string $message,
        \Exception $inner_exception
    ): self
    {
        return new static(
            $message,
            0,
            $inner_exception
        );
    }
}
