<?php

namespace assignsubmission_pxaiwriter\app\moodle;


use assignsubmission_pxaiwriter\app\moodle\context\interfaces\factory as context_factory;
use core_date;
use course_modinfo;
use curl;
use DateTimeZone;
use dml_exception;
use moodle_database;
use moodle_exception;

global $CFG;
require_once $CFG->libdir . '/filelib.php';

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private array $factories = [];

    public function context(): context_factory
    {
        return $this->factories[__FUNCTION__] ??= new context\factory();
    }

    public function course(): object
    {
        global $COURSE;
        return $COURSE;
    }

    public function curl(array $settings = []): curl
    {
        return new curl($settings);
    }

    public function db(): moodle_database
    {
        global $DB;
        return $DB;
    }

    /**
     * @param string $component
     * @return object|null
     * @throws dml_exception
     */
    public function get_config_instance(string $component = interfaces\factory::COMPONENT): ?object
    {
        return get_config($component);
    }

    public function get_string(
        string $identifier,
        $arguments = null,
        string $component = interfaces\factory::COMPONENT,
        ?string $lang = null
    ):
    string {
        return get_string(
            $identifier,
            $component,
            $arguments
        );
    }

    public function get_user_timezone(): DateTimeZone
    {
        return core_date::get_user_timezone_object();
    }

    public function mod_info(int $course_id, int $user_id = 0): course_modinfo
    {
        $info = get_fast_modinfo($course_id, $user_id);
        if (empty($info))
        {
            throw new moodle_exception('courseidnotfound');
        }
        return $info;
    }

    public function user(): object
    {
        global $USER;
        return $USER;
    }
}
