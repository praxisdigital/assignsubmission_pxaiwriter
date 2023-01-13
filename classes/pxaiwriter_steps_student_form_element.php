<?php


defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_competency\api;
use core_competency\external\competency_exporter;
use core_competency\course_module_competency;

require_once($CFG->libdir . '/form/datetimeselector.php');
require_once('HTML/QuickForm/element.php');
require_once($CFG->dirroot . '/lib/filelib.php');

/**
 * Datetime rule element.
 *
 * @package   mod_courseevents
 */
class pxaiwriter_steps_student_form_element extends HTML_QuickForm_element
{

    private $_value = array();
    private $_init = null;

    public function __construct($elementName = null, $elementLabel = null, $attributes = null, $initvalue = null)
    {
        $this->_init = $initvalue;
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function pxaiwriter_steps_student_form_element($elementName = null, $elementLabel = null, $attributes = null, $initvalue = null)
    {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $initvalue);
    }

    function setName($name)
    {
        $this->updateAttributes(array('name' => $name));
    }

    function getName()
    {
        return $this->getAttribute('name');
    }

    function setValue($value)
    {
        $this->_value = $value;
    }

    function getValue()
    {
        return $this->_value;
    }

    /**
     * Loads the template for student in the assignment view. 
     * Student is able to navigate, modity content in the steps available. 
     * The included js file is responsible for the events of the template
     *
     * @return void
     */
    function toHtml()
    {
        global $CFG, $OUTPUT, $PAGE;

        $stepConfigForm = new stdClass();
        $stepConfigForm->steps = $this->_init->steps_data;

        $html = "";
        $html .= $OUTPUT->render_from_template('assignsubmission_pxaiwriter/assignsubmission_pxaiwriter_steps_student_form', $this->_init);
        $module = array('name' => 'assignsubmission_pxaiwriter_stepconfig_form', 'fullpath' => '/mod/assign/submission/pxaiwriter/classes/pxaiwriter-step-form-config.js');
        $PAGE->requires->js_init_call('stepConfigForm.init', array($stepConfigForm), true, $module);

        $PAGE->requires->js_call_amd('assignsubmission_pxaiwriter/pxaiendpoint', 'init', ['id' => 1, 'cmid' => 100, 'contextid' => 1, 'steps_data' => $this->_init->steps_data, 'assignmentid' => $this->_init->assignmentid, 'attempt_text' => $this->_init->attempt_text]);

        return $html;
    }
}
