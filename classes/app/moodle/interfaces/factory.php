<?php

namespace assignsubmission_pxaiwriter\app\moodle\interfaces;


use assignsubmission_pxaiwriter\app\moodle\context\interfaces\factory as context_factory;
use course_modinfo;
use curl;
use DateTimeZone;
use moodle_database;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public const COMPONENT = 'assignsubmission_pxaiwriter';

    public function context(): context_factory;

    public function course(): object;

    public function curl(array $settings = []): curl;

    public function db(): moodle_database;

    public function get_config_instance(string $component = self::COMPONENT): ?object;

    public function get_string(
        string $identifier,
        $arguments = null,
        string $component = self::COMPONENT,
        ?string $lang = null
    ): string;

    public function get_user_timezone(): DateTimeZone;

    public function mod_info(int $course_id, int $user_id = 0): course_modinfo;

    public function user(): object;
}
