<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


use assignsubmission_pxaiwriter\app\factory;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class invalid_step_number_exception extends plugin_exception
{
    public static function by_web_service(?Exception $exception = null): self
    {
        return new self(
            factory::make()->moodle()->get_string('error_invalid_step_number'),
            0,
            $exception
        );
    }
}
