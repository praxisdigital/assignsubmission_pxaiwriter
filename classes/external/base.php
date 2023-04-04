<?php

namespace assignsubmission_pxaiwriter\external;


use assignsubmission_pxaiwriter\app\factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use external_api;
use external_description;

global $CFG;
require_once "$CFG->libdir/externallib.php";

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

/**
 * @method static array|null execute()
 */
abstract class base extends external_api
{
    protected static ?base_factory $factory = null;

    public static function factory(): base_factory
    {
        return static::$factory ??= factory::make();
    }
    public static function set_factory(base_factory $factory): void
    {
        static::$factory = $factory;
    }

    abstract public static function execute_parameters(): ?external_description;
    abstract public static function execute_returns(): ?external_description;
}
