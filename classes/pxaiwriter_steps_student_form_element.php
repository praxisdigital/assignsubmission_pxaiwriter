<?php


defined('MOODLE_INTERNAL') || die();

global $CFG;

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

    private array $value = array();
    private $data;

    public function __construct($elementName = null, $elementLabel = null, $attributes = null, $initvalue = null)
    {
        $this->data = $initvalue;
        parent::__construct($elementName, $elementLabel, $attributes);
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
        $this->value = $value;
    }

    function getValue()
    {
        return $this->value;
    }

    /**
     * Loads the template for student in the assignment view. 
     * Student is able to navigate, modity content in the steps available. 
     * The included js file is responsible for the events of the template
     *
     * @return string
     */
    function toHtml()
    {
        global $OUTPUT, $PAGE;

        $stepConfigForm = new stdClass();
        $stepConfigForm->steps = $this->data->steps_data;

        $html = "";
        $html .= $OUTPUT->render_from_template('assignsubmission_pxaiwriter/assignsubmission_pxaiwriter_steps_student_form', $this->data);
        $module = array('name' => 'assignsubmission_pxaiwriter_stepconfig_form', 'fullpath' => '/mod/assign/submission/pxaiwriter/classes/pxaiwriter-step-form-config.js');
        $PAGE->requires->js_init_call('stepConfigForm.init', array($stepConfigForm), true, $module);
        $PAGE->requires->js_call_amd('assignsubmission_pxaiwriter/pxaiendpoint', 'init', [
            'assignmentId' => $this->data->assignmentid,
            'submissionId' => $this->data->submissionid,
            'stepNumber' => 1,
        ]);

        return $html;
    }
}
