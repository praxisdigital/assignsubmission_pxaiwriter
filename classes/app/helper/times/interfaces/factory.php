<?php

namespace assignsubmission_pxaiwriter\app\helper\times\interfaces;


use DateTime;
use DateTimeZone;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function current_timestamp(): int;
    public function day(?DateTime $date = null, ?DateTimeZone $timezone = null): day;
}
