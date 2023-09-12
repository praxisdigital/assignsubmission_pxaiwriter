<?php

namespace assignsubmission_pxaiwriter\app\helper\diff;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private array $instances = [];

    public function text(): interfaces\text
    {
        return $this->instances[__FUNCTION__] ??= $this->html_diff();
    }

    public function html_diff(): interfaces\text
    {
        return $this->instances[__FUNCTION__] ??= new text_html_diff(
            $this->deletion_tag(),
            $this->insertion_tag()
        );
    }

    public function deletion_tag(): interfaces\html
    {
        return $this->instances[__FUNCTION__] ??= html::get_default_deletion();
    }

    public function insertion_tag(): interfaces\html
    {
        return $this->instances[__FUNCTION__] ??= html::get_default_insertion();
    }
}
