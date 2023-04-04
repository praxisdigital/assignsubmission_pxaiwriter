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

    private function format_newline(string $input_text, string $ai_text): string
    {
        return "{$input_text}\n\n{$ai_text}";
    }

    private function limit_newlines(string $text): string
    {
        $pattern = '((?:\r\n?|\n)+)$|(?:\r\n?|\n){2,}';
        return preg_replace("#$pattern#", "\n\n", $text);
    }
}
