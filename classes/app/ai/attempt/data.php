<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class data implements interfaces\data
{
    private int $attempted_count;
    private int $max_attempts;
    private ?string $attempt_text = null;
    private base_factory $factory;

    public function __construct(
        base_factory $factory,
        int $attempted_count,
        int $max_attempts
    )
    {
        $this->attempted_count = $attempted_count;
        $this->max_attempts = $max_attempts;
        $this->factory = $factory;
    }

    public function get_attempt_text(): string
    {
        return $this->attempt_text ??= $this->factory->moodle()->get_string(
            'remaining_ai_attempt_count_text',
            $this->get_remaining_string_arguments()
        );
    }

    public function get_attempted_count(): int
    {
        return $this->attempted_count;
    }

    public function get_max_attempts(): int
    {
        return $this->max_attempts;
    }

    public function is_exceeded(): bool
    {
        return $this->get_attempted_count() >= $this->get_max_attempts();
    }

    private function get_remaining_string_arguments(): object
    {
        return (object)[
            'remaining' => $this->get_max_attempts() - $this->get_attempted_count(),
            'maximum' => $this->get_max_attempts()
        ];
    }
}
