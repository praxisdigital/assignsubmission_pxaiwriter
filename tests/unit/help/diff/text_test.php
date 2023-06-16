<?php

namespace assignsubmission_pxaiwriter\unit\help\diff;


use assignsubmission_pxaiwriter\app\helper\diff\interfaces\text;
use assignsubmission_pxaiwriter\app\interfaces\factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\factory as setting_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\settings;
use assignsubmission_pxaiwriter\app\test\mock\helper\diff\mock_text;
use assignsubmission_pxaiwriter\app\test\unit_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class text_test extends unit_testcase
{
    private string $sentence1;
    private string $sentence2;
    private string $whole_sentence;

    protected function setUp(): void
    {
        $this->sentence1 = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris';
        $this->sentence2 = "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";
        $this->whole_sentence = $this->sentence1 . $this->get_newline() . $this->sentence2;
    }

    private function get_newline(): string
    {
        return "\n\n";
    }

    private function factory(string $granularity): factory
    {
        $settings = $this->createMock(settings::class);
        $settings->method('get_granularity')->willReturn($granularity);

        $setting_factory = $this->createMock(setting_factory::class);
        $setting_factory->method('admin')->willReturn($settings);

        $factory = $this->createMock(factory::class);
        $factory->method('setting')->willReturn($setting_factory);

        return $factory;
    }

    public function test_diff_by_characters(): void
    {
        $factory = $this->factory(text::GRANULARITY_CHARACTER);
        $text = new mock_text($factory);
        $actual = $text->diff($this->sentence1, $this->whole_sentence);

        $expected = $this->sentence1;
        $expected .= $text->highlight_insertion($this->get_newline() . $this->sentence2);
        $expected = $text->sanitize_text($expected);

        self::assertSame(
            $expected,
            $actual
        );
    }
}
