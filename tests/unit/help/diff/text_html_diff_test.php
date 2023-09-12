<?php

namespace assignsubmission_pxaiwriter\unit\help\diff;


use assignsubmission_pxaiwriter\app\helper\diff\html;
use assignsubmission_pxaiwriter\app\helper\diff\interfaces\html as html_interface;
use assignsubmission_pxaiwriter\app\helper\diff\text_html_diff;
use assignsubmission_pxaiwriter\app\test\unit_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class text_html_diff_test extends unit_testcase
{
    public function test_diff(): void
    {
        $ut = 'Ut enim ad minim veniam, ';
        $ut .= 'quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';

        $deleted = [
            'amet' => 'amet',
            'a' => 'a'
        ];
        $inserted = [
            'amet' => 'aMet',
            'ut' => $ut,
            'a' => 'A'
        ];

        $old_sentence = "Lorem ipsum dolor sit {$deleted['amet']}, consectetur adipiscing elit.";
        $old_sentence .= "\n\nDonec {$deleted['a']} diam lectus. Sed sit amet ipsum mauris";

        $new_sentence = "Lorem ipsum dolor sit {$inserted['amet']}, consectetur adipiscing elit.";
        $new_sentence .= " {$inserted['ut']}";
        $new_sentence .= "\n\nDonec {$inserted['a']} diam lectus. Sed sit amet ipsum mauris";

        $deletion_tag = html::get_default_deletion();
        $insertion_tag = html::get_default_insertion();

        $diff = new text_html_diff(
            $deletion_tag,
            $insertion_tag
        );
        $actual = $diff->diff(
            $old_sentence,
            $new_sentence
        );

        $expected = 'Lorem ipsum dolor sit ';
        $expected .= $this->get_diff($deleted['amet'], $deletion_tag);
        $expected .= $this->get_diff($inserted['amet'], $insertion_tag);
        $expected .= ', consectetur adipiscing elit.';
        $expected .= $this->get_diff(" {$inserted['ut']}", $insertion_tag);
        $expected .= "<br> <br> Donec ";
        $expected .= $this->get_diff($deleted['a'], $deletion_tag);
        $expected .= $this->get_diff($inserted['a'], $insertion_tag);
        $expected .= ' diam lectus. Sed sit amet ipsum mauris';

        self::assertEquals($expected, $actual);
    }

    private function get_diff(string $text, html_interface $tag): string
    {
        return $tag->get_start_tag() . $text . $tag->get_end_tag();
    }
}
