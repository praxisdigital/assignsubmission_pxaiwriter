<?php

namespace assignsubmission_pxaiwriter\app\helper\times\interfaces;


use DateTime;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface day
{
    public function get_date_time(): DateTime;
    public function get_start_of_day(): DateTime;
    public function get_end_of_day(): DateTime;
}
