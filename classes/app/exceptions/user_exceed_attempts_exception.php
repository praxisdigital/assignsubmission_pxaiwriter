<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


use assignsubmission_pxaiwriter\app\factory;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class user_exceed_attempts_exception extends plugin_exception
{
    public static function by_external_api(?Exception $exception = null): self
    {
        return new self(
            factory::make()->moodle()->get_string('error_user_exceed_attempts'),
            0,
            $exception
        );
    }
}
