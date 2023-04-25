<?php

namespace assignsubmission_pxaiwriter\unit\ai\attempt;


use assignsubmission_pxaiwriter\app\ai\attempt\data;
use assignsubmission_pxaiwriter\app\interfaces\factory;
use assignsubmission_pxaiwriter\app\test\unit_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class attempt_test extends unit_testcase
{
    public function test_get_max_attempts(): void
    {
        $factory = $this->createMock(factory::class);
        $max_attempts = 5;
        $attempted_count = 0;
        $data = new data($factory, $attempted_count, $max_attempts);
        self::assertSame(
            $max_attempts,
            $data->get_max_attempts()
        );
    }

    public function test_get_attempted_count(): void
    {
        $factory = $this->createMock(factory::class);
        $max_attempts = 5;
        $attempted_count = 2;
        $data = new data($factory, $attempted_count, $max_attempts);
        self::assertSame(
            $attempted_count,
            $data->get_attempted_count()
        );
    }

    public function test_is_exceeded(): void
    {
        $factory = $this->createMock(factory::class);
        $max_attempts = 5;
        $attempted_count = 5;
        $data = new data($factory, $attempted_count, $max_attempts);
        self::assertTrue($data->is_exceeded());
    }

    public function test_attempt_is_not_exceed_limit(): void
    {
        $factory = $this->createMock(factory::class);
        $max_attempts = 5;
        $attempted_count = 4;
        $data = new data($factory, $attempted_count, $max_attempts);
        self::assertFalse($data->is_exceeded());
    }

    public function test_get_attempt_text(): void
    {
        $string = '{$a->remaining} out of {$a->maximum} remaining';

        $moodle_factory = $this->createMock(\assignsubmission_pxaiwriter\app\moodle\interfaces\factory::class);
        $moodle_factory->method('get_string')
            ->willReturnCallback(static function(string $identifier, $arguments) use($string) {
                return self::get_string(
                    'remaining_ai_attempt_count_text',
                    $identifier,
                    $string,
                    $arguments
                );
            });

        $factory = $this->createMock(factory::class);
        $factory->method('moodle')
            ->willReturn($moodle_factory);
        $max_attempts = 5;
        $attempted_count = 3;
        $remaining = $max_attempts - $attempted_count;
        $expected_text = "$remaining out of $max_attempts remaining";

        $data = new data($factory, $attempted_count, $max_attempts);
        $text = $data->get_attempt_text();
        self::assertSame(
            $expected_text,
            $text
        );
    }
}
