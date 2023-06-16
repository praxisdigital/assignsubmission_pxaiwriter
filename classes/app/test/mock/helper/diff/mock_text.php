<?php

namespace assignsubmission_pxaiwriter\app\test\mock\helper\diff;


use assignsubmission_pxaiwriter\app\helper\diff\text;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class mock_text extends text
{
    public function sanitize_text(string $text): string
    {
        return $this->replace_newline($text);
    }

    public function highlight_deletion(string $text): string
    {
        return "{$this->get_deletion_open_tag()}{$text}{$this->get_deletion_close_tag()}";
    }

    public function highlight_insertion(string $text): string
    {
        return "{$this->get_insertion_open_tag()}{$text}{$this->get_insertion_close_tag()}";
    }
}
