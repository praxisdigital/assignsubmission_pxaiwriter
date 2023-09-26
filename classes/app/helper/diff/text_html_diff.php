<?php

namespace assignsubmission_pxaiwriter\app\helper\diff;


use assignsubmission_pxaiwriter\app\helper\diff\interfaces\text;
use Caxy\HtmlDiff\HtmlDiff;
use Caxy\HtmlDiff\HtmlDiffConfig;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once "$CFG->dirroot/mod/assign/submission/pxaiwriter/vendor/autoload.php";

class text_html_diff implements text
{
    private html $deletion_tag;
    private html $insertion_tag;

    public function __construct(
        interfaces\html $deletion_tag,
        interfaces\html $insertion_tag
    )
    {
        $this->deletion_tag = $deletion_tag;
        $this->insertion_tag = $insertion_tag;
    }

    public function diff(string $old_data, string $new_data): string
    {
        $old_data = $this->replace_newline($old_data);
        $new_data = $this->replace_newline($new_data);

        $text = $this->parse_text($old_data, $new_data);

        $text = $this->replace_deletion($text);
        return $this->replace_insertion($text);
    }

    public function set_deletion_tag(interfaces\html $tag): text
    {
        $this->deletion_tag = $tag;
        return $this;
    }

    public function set_insertion_tag(interfaces\html $tag): text
    {
        $this->insertion_tag = $tag;
        return $this;
    }

    private function parse_text(string $old_text, string $new_text): string
    {
        $config = new HtmlDiffConfig();
        $config->setInsertSpaceInReplace(false);
        $config->setPurifierEnabled(false);
        $config->setEncoding('UTF-8');

        return HtmlDiff::create(
            $old_text,
            $new_text,
            $config
        )->build();
    }

    private function get_deletion_tag(): string
    {
        return $this->deletion_tag->get_start_tag();
    }

    private function get_deletion_close_tag(): string
    {
        return $this->deletion_tag->get_end_tag();
    }

    private function get_insertion_tag(): string
    {
        return $this->insertion_tag->get_start_tag();
    }

    private function get_insertion_close_tag(): string
    {
        return $this->insertion_tag->get_end_tag();
    }

    private function replace_newline(string $text): string
    {
        return html::replace_newline($text);
    }

    private function replace_deletion(string $text): string
    {
        return $this->replace_diff_tags(
            $text,
            $this->get_deletion_tag(),
            $this->get_deletion_close_tag(),
            [
                '<del>',
                '<del class="diffmod">',
                '<del class="diffdel">',
            ],
            ['</del>']
        );
    }

    private function replace_insertion(string $text): string
    {
        return $this->replace_diff_tags(
            $text,
            $this->get_insertion_tag(),
            $this->get_insertion_close_tag(),
            [
                '<ins>',
                '<ins class="diffmod">',
                '<ins class="diffins">',
            ],
            ['</ins>'],
        );
    }

    private function replace_diff_tags(
        string $text,
        string $new_start_tag,
        string $new_end_tag,
        array $diff_tags,
        array $diff_end_tags
    ): string
    {

        foreach ($diff_end_tags as $tag)
        {
            $text = $this->replace_tag(
                $text,
                $tag,
                $new_end_tag,
            );
        }
        foreach ($diff_tags as $tag) {
            $text = $this->replace_tag(
                $text,
                $tag,
                $new_start_tag,
            );
        }
        return $text;
    }

    private function replace_tag(
        string $text,
        string $diff_tag,
        string $new_tag
    ): string
    {
        return str_replace($diff_tag, $new_tag, $text);
    }
}
