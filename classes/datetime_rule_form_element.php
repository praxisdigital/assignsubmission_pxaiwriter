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
class test_element_123 extends HTML_QuickForm_element
{

    private $_value = array();

    public function __construct($elementName = null, $elementLabel = null, $attributes = null)
    {

        parent::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function test_element_123($elementName = null, $elementLabel = null, $attributes = null)
    {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    // function onQuickFormEvent($event, $arg, &$caller) {
    //     global $OUTPUT;

    //     return parent::onQuickFormEvent($event, $arg, $caller);
    // }

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

    // function getFrozenHtml() {
    //     $html = "<div>Test</div>";
    //     return $html;
    // }

    function toHtml()
    {
        global $CFG, $OUTPUT, $PAGE;

        $stepList = array();
        $step1 = new stdClass();
        $step1->step = 1;
        $step1->description = "Test description 1";
        $step1->mandatory = true;
        $step1->type = 'text';
        $step1->removable = false;

        $step2 = new stdClass();
        $step2->step = 2;
        $step2->description = "Test description 1";
        $step2->mandatory = true;
        $step2->type = 'text';
        $step1->removable = false;

        array_push($stepList, $step1, $step2);

        $stepConfig = new stdClass();
        //$this->setValue('test');

        $stepConfig->steps = $stepList;
        $stepLabel = get_string('guide_to_step_label', 'assignsubmission_aiwriter');
        $removeButtonLabel = get_string('remove_step_label', 'assignsubmission_aiwriter');
        $stepConfig->template = '<div class="row mb-2" id="step_{{step}}">
                                    <div class="col-md-11">
                                        <div class=" form-group row">
                                            <label for="staticEmail" class="col-md-3 col-form-label">'.$stepLabel.'&nbsp;{{step}}</label>
                                            <div class="col-md-9">
                                                <textarea class="form-control step-des" name="step_{{step}}_value" id="step_{{step}}_value" data-id="{{step}}">{{ description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        {{#removable}} <button class="btn btn-remove remove-btn" id="remove_{{step}}" data-id="{{step}}"><i class="fa fa-trash" aria-hidden="true"></i></button> {{/removable}}
                                        {{^removable}} <button class="btn btn-remove remove-btn d-none" id="remove_{{step}}" data-id="{{step}}"><i class="fa fa-trash" aria-hidden="true"></i></button> {{/removable}}
                                    </div>
                                </div>';
        $html = "";
        $html .= $OUTPUT->render_from_template('assignsubmission_aiwriter/test',null);

        $module = array('name' => 'assignsubmission_aiwriter_test', 'fullpath' => '/mod/assign/submission/aiwriter/classes/test.js');
        $PAGE->requires->js_init_call('test.init', array($stepConfig), true, $module);
        return $html;
    }
}

?>
