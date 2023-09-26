<?php

namespace assignsubmission_pxaiwriter\app\helper\diff\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface html
{
    public const STYLE_COLOR = 'color';
    public const STYLE_BACKGROUND_COLOR = 'background-color';
    public const STYLE_TEXT_DECORATION = 'text-decoration';

    public function set_styles(array $styles): html;

    public function get_start_tag(): string;
    public function get_end_tag(): string;

}
