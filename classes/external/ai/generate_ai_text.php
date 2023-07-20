<?php

namespace assignsubmission_pxaiwriter\external\ai;


use assignsubmission_pxaiwriter\app\exceptions\moodle_traceable_exception;
use assignsubmission_pxaiwriter\app\exceptions\overdue_assignment_exception;
use assignsubmission_pxaiwriter\app\exceptions\user_exceed_attempts_exception;
use assignsubmission_pxaiwriter\external\base;
use Exception;
use external_description;
use external_function_parameters;
use external_single_structure;
use external_value;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class generate_ai_text extends base
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
            'submission' => new external_value(
                PARAM_INT,
                'Submission ID',
                VALUE_DEFAULT,
                0
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

    public  static function execute(int $assignment_id, string $text, int $step = 1, int $submission = 0): array
    {
        self::validate_input([
            'assignment_id' => $assignment_id,
            'text' => $text,
            'step' => $step,
            'submission' => $submission
        ]);
        self::validate_step_number($step);
        self::validate_assignment($assignment_id);

        $factory = self::factory();
        $ai_factory = $factory->ai();

        $transaction = $factory->moodle()->db()->start_delegated_transaction();
        $current_user = $factory->moodle()->user();

        $archive = $ai_factory->history()->archive_generate_ai_text(
            $assignment_id,
            $submission,
            $current_user->id,
            $step
        );

        try
        {
            $attempt_data = $ai_factory->attempt()->repository()->get_today_remaining_attempt(
                $current_user->id,
                $assignment_id
            );

            if ($attempt_data->is_exceeded())
            {
                throw user_exceed_attempts_exception::by_external_api();
            }

            $response_data = $ai_factory->openai()->api()->generate_ai_text($text);
            $combined_text = $ai_factory->formatter()->text($text, $response_data->get_text());

            $archive->commit_by_generate_ai_text(
                $text,
                $response_data->get_text(),
                $combined_text,
                $response_data->get_response_json()
            );
            
            $attempt_data->make_attempt();

            $transaction->allow_commit();

            return [
                'data' => $combined_text,
                'attempt_text' => $attempt_data->get_attempt_text(),
                'attempted_count' => $attempt_data->get_attempted_count(),
                'max_attempts' => $attempt_data->get_max_attempts()
            ];
        }
        catch (Exception $exception)
        {
            try
            {
                $transaction->rollback($exception);
            }
            catch (Exception $rollback_exception) {}

            $archive->failed($text);
            throw new moodle_traceable_exception('error_generate_ai_text_api', $exception);
        }
    }
}
