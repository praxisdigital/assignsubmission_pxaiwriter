<?php

namespace assignsubmission_pxaiwriter\app\moodle\interfaces;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use assignsubmission_pxaiwriter\app\moodle\context\interfaces\factory as context_factory;
use core_string_manager;
use course_modinfo;
use curl;
use DateTimeZone;
use file_storage;
use moodle_database;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function cfg(): object;

    public function context(): context_factory;

    public function course(): object;

    public function curl(array $settings = []): curl;

    public function db(): moodle_database;

    public function file_storage(bool $reinitialize = false): file_storage;

    public function get_config_instance(string $component = base_factory::COMPONENT): object;

    /**
     * @param string $name
     * @param mixed $value
     * @param string $component
     * @return void
     */
    public function set_config(
        string $name,
        $value,
        string $component = base_factory::COMPONENT
    ): void;

    public function get_string_manager(): core_string_manager;

    public function get_string(
        string $identifier,
        $arguments = null,
        string $component = base_factory::COMPONENT,
        ?string $lang = null
    ): string;

    public function get_group_name_by_id(int $group_id): string;

    public function get_user_timezone(): DateTimeZone;

    public function mod_info(int $course_id, int $user_id = 0): course_modinfo;

    public function user(): object;
}
