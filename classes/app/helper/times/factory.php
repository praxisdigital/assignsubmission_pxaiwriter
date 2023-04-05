<?php

namespace assignsubmission_pxaiwriter\app\helper\times;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use DateTime;
use DateTimeZone;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function current_timestamp(): int
    {
        return time();
    }

    public function day(?DateTime $date = null, ?DateTimeZone $timezone = null): interfaces\day
    {
        $date ??= new DateTime('now', $timezone ?? $this->factory->moodle()->get_user_timezone());
        return new day($date);
    }
}
