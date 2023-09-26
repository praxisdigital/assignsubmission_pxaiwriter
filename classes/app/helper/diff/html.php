<?php

namespace assignsubmission_pxaiwriter\app\helper\diff;



/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class html implements interfaces\html
{
    private string $tag_name;
    private array $styles = [];

    public function __construct(string $tag_name = 'span')
    {
        $this->tag_name = $tag_name;
    }

    public static function get_default_deletion(): html
    {
        $html = new html();
        $html->set_color('red');
        $html->set_background_color('#ffdddd');
        $html->set_text_decoration('line-through');
        return $html;
    }

    public static function get_default_insertion(): html
    {
        $html = new html();
        $html->set_color('green');
        $html->set_background_color('#ddffdd');
        $html->set_text_decoration('none');
        return $html;
    }

    public static function replace_newline(string $text): string
    {
        return nl2br(trim($text), false);
    }

    public function set_styles(array $styles): interfaces\html
    {
        $this->styles = $styles;
        return $this;
    }

    public function get_start_tag(): string
    {
        return '<'. $this->tag_name .' style="' . $this->get_inline_style() . '">';
    }

    public function get_end_tag(): string
    {
        return '</'. $this->tag_name .'>';
    }

    private function get_inline_style(): string
    {
        $styles = '';
        foreach ($this->styles as $style => $value)
        {
            $styles .= " $style: $value;";
        }
        return trim($styles);
    }

    private function set_color(string $color): void
    {
        $this->styles[self::STYLE_COLOR] = $color;
    }

    private function set_background_color(string $background_color): void
    {
        $this->styles[self::STYLE_BACKGROUND_COLOR] = $background_color;
    }

    private function set_text_decoration(string $text_decoration): void
    {
        $this->styles[self::STYLE_TEXT_DECORATION] = $text_decoration;
    }
}
