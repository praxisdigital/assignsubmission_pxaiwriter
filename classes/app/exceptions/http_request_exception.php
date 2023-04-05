<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class http_request_exception extends plugin_exception
{
    public static function by_openai_api(string $message, ?Exception $exception = null): self
    {
        return new static($message, 0, $exception);
    }
}
