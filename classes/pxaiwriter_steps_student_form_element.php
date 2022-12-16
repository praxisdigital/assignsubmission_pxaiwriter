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


        // echo (var_dump($elementName));
        // echo (var_dump($elementLabel));
        // echo (var_dump($attributes));
        // echo (var_dump($initvalue));



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

    // function onQuickFormEvent($event, $arg, &$caller) {
    //     global $OUTPUT;

    //     return parent::onQuickFormEvent($event, $arg, $caller);
    // }

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

    // function getFrozenHtml() {
    //     $html = "<div>Test</div>";
    //     return $html;
    // }

    function toHtml()
    {
        global $CFG, $OUTPUT, $PAGE;

        $stepConfigForm = new stdClass();

        //$stepConfigForm->steps = $this->_init_val;
        // $stepLabel = get_string('guide_to_step_label', 'assignsubmission_pxaiwriter');
        // $removeButtonLabel = get_string('remove_step_label', 'assignsubmission_pxaiwriter');
        // $stepConfigForm->template = '<div class="row mb-2" id="step_{{step}}">
        //                             <div class="col-md-11">
        //                                 <div class=" form-group row">
        //                                     <label for="staticEmail" class="col-md-3 col-form-label">'.$stepLabel.'&nbsp;{{step}}</label>
        //                                     <div class="col-md-9">
        //                                         <textarea class="form-control step-des" name="step_{{step}}_value" id="step_{{step}}_value" data-id="{{step}}">{{ description }}</textarea>
        //                                     </div>
        //                                 </div>
        //                             </div>
        //                             <div class="col-md-1 align-self-baseline">
        //                                 {{#removable}} <button class="btn btn-remove remove-btn" id="remove_{{step}}" data-id="{{step}}"><i class="fa fa-trash" aria-hidden="true"></i></button> {{/removable}}
        //                                 {{^removable}} <button class="btn btn-remove remove-btn d-none" id="remove_{{step}}" data-id="{{step}}"><i class="fa fa-trash" aria-hidden="true"></i></button> {{/removable}}
        //                             </div>
        //                         </div>';
        // $stepConfigForm->hasUsed = $this->_has_used;
        $stepConfigForm->steps = $this->_init->steps_data;

        $html = "";
        $html .= $OUTPUT->render_from_template('assignsubmission_pxaiwriter/assignsubmission_pxaiwriter_steps_student_form', $this->_init);
        $module = array('name' => 'assignsubmission_pxaiwriter_stepconfig_form', 'fullpath' => '/mod/assign/submission/pxaiwriter/classes/pxaiwriter-step-form-config.js');
        $PAGE->requires->js_init_call('stepConfigForm.init', array($stepConfigForm), true, $module);
        return $html;
    }
}
