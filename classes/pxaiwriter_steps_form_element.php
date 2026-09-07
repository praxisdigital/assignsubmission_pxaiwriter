<?php

namespace assignsubmission_pxaiwriter;

use assignsubmission_pxaiwriter\app\factory;
use HTML_QuickForm_element;
use stdClass;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once $CFG->libdir . '/form/datetimeselector.php';
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/lib/filelib.php';

/**
 * Datetime rule element.
 *
 * @package   mod_courseevents
 */
class pxaiwriter_steps_form_element extends HTML_QuickForm_element
{

    private $_value = array();
    private $_init_val = null;
    private $_has_used = false;

    public function __construct($elementName = null, $elementLabel = null, $attributes = null, $initvalue = null, $hasUsed = false)
    {
        $this->_init_val = $initvalue;
        $this->_has_used = $hasUsed;
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    function setName($name) {
        $this->updateAttributes(array('name' => $name));
    }

    function getName() {
        return $this->getAttribute('name');
    }

    function setValue($value) {
        $this->_value = $value;
    }

    function getValue() {
        return $this->_value;
    }

    /**
     * This template loads at the assignment configuration area. The user is able to add, remove and modify steps instructions via this tempalte
     *
     * @return string
     */
    function toHtml()
    {
        global $OUTPUT, $PAGE;

        $stepConfig = new stdClass();
        $stepConfig->steps = $this->get_steps_with_guide_info($this->_init_val);
        $stepConfig->hasUsed = $this->_has_used;

        $name = $this->getName();
        $id = $this->getAttribute('id');
        if (empty($id)) {
            $id = 'id_' . $name;
            $this->updateAttributes(['id' => $id]);
        }
        $html = \html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $name,
            'id' => $id,
            'value' => '1',
        ]);
        $html .= $OUTPUT->render_from_template(
            'assignsubmission_pxaiwriter/assignsubmission_pxaiwriter_steps_form',
            null
        );

        $PAGE->requires->js_call_amd('assignsubmission_pxaiwriter/pxaiwriter-step-config', 'init', [
            $stepConfig
        ]);

        return $html;
    }

    private function get_steps_with_guide_info(array $steps): array
    {
        $items = [];
        foreach ($steps as $step)
        {
            if ($this->is_ai_step($step))
            {
                $step->help = $this->get_plugin_string('ai_step_helper');
                $step->guide = $this->get_plugin_string(
                    'guide_to_step_with_helper',
                    $step
                );
                $items[] = $step;
                continue;
            }
            $step->help = '';
            $step->guide = $this->get_plugin_string('guide_to_step_no_helper', $step);
            $items[] = $step;
        }

        return $items;
    }

    private function get_plugin_string(string $identifier, $arguments = null): string
    {
        return factory::make()->moodle()->get_string(
            $identifier,
            $arguments
        );
    }

    private function is_ai_step($step): bool
    {
        return isset($step->step) && $step->step === 1;
    }
}
