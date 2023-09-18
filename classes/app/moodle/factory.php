<?php

namespace assignsubmission_pxaiwriter\app\moodle;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use assignsubmission_pxaiwriter\app\moodle\context\interfaces\factory as context_factory;
use coding_exception;
use core_date;
use core_string_manager;
use course_modinfo;
use curl;
use DateTimeZone;
use dml_exception;
use Exception;
use file_storage;
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

    public function file_storage(bool $reinitialize = false): file_storage
    {
        $storage = get_file_storage($reinitialize);
        if (empty($storage))
        {
            throw new coding_exception(
                'Missing file storage cause by Moodle'
            );
        }
        return $storage;
    }

    /**
     * @param string $component
     * @return object
     * @throws dml_exception
     */
    public function get_config_instance(string $component = base_factory::COMPONENT): object
    {
        $config = get_config($component);
        if (empty($config))
        {
            return (object)[];
        }
        return $config;
    }

    public function set_config(string $name, $value, string $component = base_factory::COMPONENT): void
    {
        set_config(
            $name,
            $value,
            $component
        );
    }

    public function get_string_manager(): core_string_manager
    {
        return get_string_manager();
    }

    public function get_string(
        string $identifier,
        $arguments = null,
        string $component = base_factory::COMPONENT,
        ?string $lang = null
    ):
    string {
        return get_string(
            $identifier,
            $component,
            $arguments
        );
    }

    public function get_group_name_by_id(int $group_id): string
    {
        try
        {
            return groups_get_group_name($group_id);
        }
        catch (Exception $exception)
        {
            throw new moodle_exception('cannot_found_group_name');
        }
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
