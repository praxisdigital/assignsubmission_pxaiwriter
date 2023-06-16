<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


use assignsubmission_pxaiwriter\app\factory;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class invalid_step_number_exception extends plugin_exception
{
    public static function by_web_service(int $step, ?Exception $exception = null): self
    {
        return new self(
            factory::make()->moodle()->get_string('error_invalid_step_number', $step),
            0,
            $exception
        );
    }
}
