<?php

namespace assignsubmission_pxaiwriter\event;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class ai_text_generated extends \core\event\base
{
    public static function get_name()
    {
        return get_string('event_ai_text_generated', 'assignsubmission_pxaiwriter');
    }

    protected function init()
    {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = assignsubmission_pxaiwriter\app\ai\history\interfaces\repository::TABLE;
    }
}
