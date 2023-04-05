<?php

namespace assignsubmission_pxaiwriter\app\ai;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class formatter implements interfaces\formatter
{
    public function text(string $input_text, string $ai_text): string
    {
        return trim(
            $this->limit_newlines(
                $this->format_newline($input_text, $ai_text)
            )
        );
    }

    public function replace(string $original_text, string $input_text, string $ai_text, int $offset): string
    {
        $total_length = mb_strlen($original_text);
        if ($offset >= $total_length)
        {
            throw new \InvalidArgumentException('Unknown selection');
        }

        $first_text = mb_substr($original_text, 0, $offset);
        $start_point = mb_strlen($input_text);
        $end_select = $offset + $start_point;

        $ai_formatted_text = $this->limit_newlines($ai_text);

        if ($end_select >= $total_length)
        {
            return $first_text . $ai_formatted_text;
        }

        $end_text = mb_substr($original_text, $end_select);
        return $first_text . $ai_formatted_text . $end_text;
    }

    private function format_newline(string $input_text, string $ai_text): string
    {
        return "{$input_text}\n\n{$ai_text}";
    }

    private function limit_newlines(string $text): string
    {
        return preg_replace("#((?:\r\n?|\n)+)$|(?:\r\n?|\n){2,}#", "\n\n", $text);
    }
}
