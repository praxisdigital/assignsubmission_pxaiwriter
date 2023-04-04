<?php

namespace assignsubmission_pxaiwriter\app\helper\times;


use DateTime;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class day implements interfaces\day
{
    private DateTime $date;

    public function __construct(DateTime $date)
    {
        $this->date = $date;
    }

    public function get_date_time(): DateTime
    {
        return $this->date;
    }

    public function get_start_of_day(): DateTime
    {
        $start_of_day = clone $this->date;
        $start_of_day->setTime(0, 0, 0);
        return $start_of_day;
    }

    public function get_end_of_day(): DateTime
    {
        $start_of_day = clone $this->date;
        $start_of_day->setTime(23, 59, 59);
        return $start_of_day;
    }
}
