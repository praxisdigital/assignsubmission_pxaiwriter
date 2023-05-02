<?php

namespace assignsubmission_pxaiwriter\unit\ai;


use assignsubmission_pxaiwriter\app\ai\formatter;
use assignsubmission_pxaiwriter\app\test\unit_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class formatter_test extends unit_testcase
{

    private function combined_text(string $input_text, string $ai_text): string
    {
        return "{$input_text}\n\n{$ai_text}";
    }

    public function test_text_formatted_single_step(): void
    {
        $user_text = 'This is a user text';
        $ai_text = 'This is a AI text';

        $formatter = new formatter();
        $actual = $formatter->text(
            $user_text,
            $ai_text
        );

        $expected = $this->combined_text($user_text, $ai_text);

        self::assertSame(
            $expected,
            $actual
        );
    }

    public function test_text_formatted_multiple_steps(): void
    {
        $user_text = 'This is a user text';

        $ai_text1 = 'This is a AI text 1';

        $formatter = new formatter();
        $actual = $formatter->text(
            $user_text,
            $ai_text1
        );

        $expected = $this->combined_text($user_text, $ai_text1);

        self::assertSame(
            $expected,
            $actual
        );
    }
}
