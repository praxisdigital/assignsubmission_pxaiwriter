<?php

namespace assignsubmission_pxaiwriter\app\file\pdf;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\collection;
use assignsubmission_pxaiwriter\app\helper\diff\interfaces\text;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use assignsubmission_pxaiwriter\app\submission\interfaces\submission_history;
use stored_file;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class repository implements interfaces\repository
{
    private const STEP_KEY_DIFF = 'diff';
    private const STEP_KEY_DATA = 'data';
    private const PDF_PAGE_BREAK = '<br pagebreak="true" />';

    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function save_submission_as_pdf(submission_history $submission_history): ?stored_file
    {
        $filename = $this->get_filename($submission_history);
        $data = $this->get_pdf_diff_by_history_list($submission_history);
        if (empty($data))
        {
            return null;
        }

        $this->factory->file()->repository()->delete_files_by_submission(
            $submission_history->get_context(),
            $submission_history->get_submission()->id
        );

        return $this->factory->file()->repository()->create_from_submission(
            $filename,
            $data,
            $submission_history->get_context(),
            $submission_history->get_submission()
        );
    }

    public function get_pdf_diff_by_history_list(submission_history $submission_history): ?string
    {
        $data = $this->get_html_diff_by_history_list($submission_history);
        return $this->factory->file()->pdf()->converter()->convert_to_pdf_file(
            $this->get_filename($submission_history),
            $data
        );
    }

    public function get_html_diff_by_history_list(submission_history $submission_history): ?string
    {
        $history_list = $submission_history->get_history_list();
        $step_numbers = $history_list->get_step_numbers();

        if (empty($step_numbers))
        {
            return null;
        }

        $pdf_data = [];
        $text_diff = $this->factory->helper()->diff()->text();
        $diff_text = '';

        [
            self::STEP_KEY_DIFF => $diff_text,
            self::STEP_KEY_DATA => $pdf_data
        ] = $this->get_first_step_text_diff(
            $step_numbers[array_key_first($step_numbers)],
            $history_list,
            $submission_history,
            $diff_text,
            $pdf_data,
            $text_diff
        );

        $pdf_data = $this->get_final_steps_text_diff(
            $step_numbers,
            $submission_history,
            $history_list,
            $text_diff,
            $diff_text,
            $pdf_data
        );

        return implode(self::PDF_PAGE_BREAK, $pdf_data);
    }

    private function get_filename(submission_history $submission_history): string
    {
        $current_time = $this->factory->helper()->times()->current_time();
        $submission = $submission_history->get_submission();
        return "a{$submission->assignment}-u{$submission->userid}-g{$submission->groupid}_{$current_time}.pdf";
    }

    private function get_pdf_html(
        string $step_title,
        string $description,
        string $text
    ): string {
        $html = '<h4 style="margin: 10px 0px 10px 0px;"><b>Step ' . $step_title .  "</b></h4>";
        $html .= '<div style="color:#808080;margin: 0px 0px 10px 0px;"><span><i>' . $description .  "</i></span></div>";
        $html .= '<hr><div style="margin: 0px 0px 10px 0px;"></div>';
        $html .= $text;
        return $html;
    }

    private function skip_first_element(array $items): array
    {
        $index = array_key_first($items);
        unset($items[$index]);
        return array_values($items);
    }

    private function get_final_steps_text_diff(
        array $step_numbers,
        submission_history $submission_history,
        collection $history_list,
        text $text_diff,
        ?string $diff_text,
        array $pdf_data
    ): array {
        $step_numbers = $this->skip_first_element($step_numbers);
        foreach ($step_numbers as $step_number)
        {
            $step_config = $submission_history->get_step_config($step_number);
            $history = $history_list->get_latest_entity_by_step($step_number);

            $data = $history->get_data();
            $pdf_data[] = $this->get_pdf_html(
                $step_number,
                $step_config->get_description(),
                $text_diff->diff($diff_text, $data)
            );
            $diff_text = $data;
        }
        return $pdf_data;
    }

    private function get_first_step_text_diff(
        $step_numbers,
        collection $history_list,
        submission_history $submission_history,
        string $diff_text,
        array $pdf_data,
        text $text_diff
    ): array {

        $first_step_number = $step_numbers;
        $first_steps = $history_list->get_step_entities($first_step_number);
        $first_step_config = $submission_history->get_step_config($first_step_number);
        $first_history = $first_steps->get_first_entity_by_step($first_step_number);
        $step_count = 1;

        $sub_steps = $first_steps->skip(1);
        $sub_steps_count = $sub_steps->count();

        if ($first_history !== null)
        {
            $first_user_text = $first_history->get_input_text();
            $diff_text = $first_user_text;

            $pdf_data[] = $this->get_pdf_html(
                $first_history->get_step(),
                $first_step_config->get_description(),
                nl2br(trim($first_user_text))
            );

            $first_data_text = $first_history->get_data();

            if (!empty($first_data_text) && $first_user_text !== $first_data_text)
            {
                $pdf_data[] = $this->get_pdf_html(
                    $this->get_step_number_name($first_history, $step_count),
                    $first_step_config->get_description(),
                    $text_diff->diff($diff_text, $first_data_text)
                );
                $diff_text = $first_data_text;
                ++$step_count;
            }
        }

        if ($sub_steps_count > 1)
        {
            foreach ($sub_steps as $step)
            {
                if ($step->is_ai_generate())
                {
                    $data = $step->get_data();
                    $pdf_data[] = $this->get_pdf_html(
                        $this->get_step_number_name($step, $step_count),
                        $first_step_config->get_description(),
                        $text_diff->diff($step->get_input_text(), $data)
                    );
                    $diff_text = $data;
                    ++$step_count;
                    continue;
                }

                $data = $step->get_data();
                $pdf_data[] = $this->get_pdf_html(
                    $this->get_step_number_name($step, $step_count),
                    $first_step_config->get_description(),
                    $text_diff->diff($diff_text, $data)
                );
                $diff_text = $data;
                ++$step_count;
            }
        }

        return [
            self::STEP_KEY_DIFF => $diff_text,
            self::STEP_KEY_DATA => $pdf_data
        ];
    }

    private function get_step_number_name($step, int $step_count): string
    {
        return "{$step->get_step()}.{$step_count}";
    }
}
