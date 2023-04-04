<?php

namespace assignsubmission_pxaiwriter\external\ai;


use assignsubmission_pxaiwriter\app\exceptions\user_exceed_attempts_exception;
use assignsubmission_pxaiwriter\external\base;
use external_description;
use external_function_parameters;
use external_single_structure;
use external_value;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class expand_ai_text extends base
{
    public static function execute_parameters(): ?external_description
    {
        return new external_function_parameters([
            'assignment_id' => new external_value(
                PARAM_INT,
                'Assignment ID',
                VALUE_REQUIRED
            ),
            'text' => new external_value(
                PARAM_RAW,
                'Input text',
                VALUE_REQUIRED
            ),
            'step' => new external_value(
                PARAM_INT,
                'Step number',
                VALUE_DEFAULT,
                1
            ),
        ]);
    }

    public static function execute_returns(): ?external_description
    {
        return new external_single_structure([
            'data' => new external_value(PARAM_RAW, 'AI generated text', VALUE_REQUIRED),
            'attempt_text' => new external_value(PARAM_RAW, 'Attempt text', VALUE_REQUIRED),
            'attempted_count' => new external_value(PARAM_INT, 'Number of attempted', VALUE_REQUIRED),
            'max_attempts' => new external_value(PARAM_INT, 'Maximum number of attempts', VALUE_REQUIRED),
        ]);
    }

    public static function execute(int $assignment_id, string $text, int $step = 1): array
    {
        self::validate_parameters(
            self::execute_parameters(),
            get_defined_vars()
        );

        $factory = self::factory();
        $ai_factory = $factory->ai();

        $course_module_id = $factory->assign()->repository()->get_course_module_id_by_assign_id($assignment_id);
        $context = $factory->moodle()->context()->course_module($course_module_id);
        self::validate_context($context);

        $day = $factory->helper()->times()->day();

        $transaction = $factory->moodle()->db()->start_delegated_transaction();
        $current_user = $factory->moodle()->user();

        $history = $ai_factory->history()->archive(
            $assignment_id,
            $step,
            $current_user->id,
            $transaction
        );

        try
        {
            $attempt_data = $ai_factory->attempt()->repository()->get_remaining_attempt(
                $current_user->id,
                $assignment_id,
                $step,
                $day->get_start_of_day()->getTimestamp(),
                $day->get_end_of_day()->getTimestamp()
            );

            if ($attempt_data->is_exceeded())
            {
                throw user_exceed_attempts_exception::by_external_api();
            }

            $history->start_attempt($text);

            $generated_text = $ai_factory->api()->expand_ai_text($text);
            $combined_text = $ai_factory->formatter()->text($text, $generated_text);

            $history->force_commit(
                $combined_text,
                $generated_text
            );

            return [
                'data' => $combined_text,
                'attempt_text' => $attempt_data->get_attempt_text(),
                'attempted_count' => $attempt_data->get_attempted_count(),
                'max_attempts' => $attempt_data->get_max_attempts()
            ];
        }
        catch (\Exception $exception)
        {
            $history->rollback(
                $text,
                $exception
            );
            throw $exception;
        }
    }
}
