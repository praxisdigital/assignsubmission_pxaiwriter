<?php

namespace assignsubmission_pxaiwriter\app\helper\diff\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface text
{
    public const GRANULARITY_CHARACTER = 'character';
    public const GRANULARITY_WORD = 'word';
    public const GRANULARITY_SENTENCE = 'sentence';
    public const GRANULARITY_PARAGRAPH = 'paragraph';

    public function diff(string $old_data, string $new_data): string;
}
