<?php

namespace assignsubmission_pxaiwriter\app\test;


use basic_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class unit_testcase extends basic_testcase
{
    public static function get_string(
        string $expected_identifier,
        string $actual_identifier,
        string $string_value,
        $arguments = null
    ): string
    {
        if ($expected_identifier !== $actual_identifier)
        {
            throw new \InvalidArgumentException("Invalid string identifier: $actual_identifier");
        }
        if (empty($arguments))
        {
            return $string_value;
        }

        if (!is_object($arguments) && !is_array($arguments))
        {
            return str_replace('{$a}', (string)$arguments, $string_value);
        }

        return self::get_string_with_arguments(
            $string_value,
            (array)$arguments
        );
    }

    private static function get_string_with_arguments(string $string, array $arguments): string
    {
        foreach ($arguments as $property => $value)
        {
            $string = str_replace("{\$a->$property}", $value, $string);
        }
        return $string;
    }
}
