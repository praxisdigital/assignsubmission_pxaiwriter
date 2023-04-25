<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class unsupported_openai_model_exception extends plugin_exception
{
    public static function by_model_type(string $model): self
    {
        return new self("Given model type ($model) is not supported");
    }
}
