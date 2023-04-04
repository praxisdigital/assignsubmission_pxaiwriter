<?php

namespace assignsubmission_pxaiwriter\external\ai;


use assignsubmission_pxaiwriter\external\base;
use external_description;
use external_function_parameters;
use external_single_structure;
use external_value;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class record_history extends base
{
    public static function execute_parameters(): ?external_description
    {
        return new external_function_parameters([
            'assignment_id' => new external_value(
                PARAM_INT,
                'Assignment ID'
            ),
            'text' => new external_value(
                PARAM_RAW,
                'User text'
            ),
            'step' => new external_value(
                PARAM_INT,
                'AI writer step number',
                VALUE_DEFAULT,
                1
            ),
        ]);
    }

    public static function execute_returns(): ?external_description
    {
        return new external_single_structure([
            'checksum' => new external_value(
                PARAM_RAW,
                'Checksum of the text'
            ),
        ]);
    }

    public static function execute(int $assignment_id, string $text, int $step = 1): array
    {
        self::validate_parameters(self::execute_parameters(), [
            'assignment_id' => $assignment_id,
            'text' => $text,
            'step' => $step
        ]);

        $factory = self::factory();
        $archive = $factory->ai()->history()->archive(
            $assignment_id,
            $step,
            $factory->moodle()->user()->id
        );

        $history = $archive->commit($text);

        return [
            'checksum' => $history->get_hashcode()
        ];
    }
}
