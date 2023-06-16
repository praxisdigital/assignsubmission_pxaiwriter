<?php

namespace assignsubmission_pxaiwriter\external;


use assignsubmission_pxaiwriter\app\exceptions\invalid_step_number_exception;
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
    
    protected static function validate_input(array $parameters): void
    {
        self::validate_parameters(static::execute_parameters(), $parameters);
    }
    
    protected static function validate_assignment(int $assignment_id): void
    {
        $factory = static::factory();
        $course_module_id = $factory->assign()->repository()
            ->get_course_module_id_by_assign_id($assignment_id);
        $context = $factory->moodle()->context()->course_module($course_module_id);
        self::validate_context($context);
    }
    
    protected static function validate_step_number(int $step): void
    {
        if ($step < 1)
        {
            throw invalid_step_number_exception::by_web_service($step);
        }
    }
}
