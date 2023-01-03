<?php
require_once($CFG->libdir . "/phpspreadsheet/vendor/autoload.php");
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/webservice/externallib.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');

class mod_assign_submission_pxaiwriter_external extends external_api
{

    /**
     * Undocumented function
     *
     * @param [type] $contextid
     * @param [type] $jsonformdata
     * @return void
     */
    public static function do_ai_magic($contextid, $jsondata)
    {
        global $DB, $CFG, $USER;

        $params = self::validate_parameters(self::do_ai_magic_parameters(), ['contextid' => $contextid, 'jsondata' => $jsondata]);
        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        self::validate_context($context);

        $serialiseddata = json_decode($params['jsondata']);

        $data = array();
        parse_str($serialiseddata, $data);

        return json_encode(
            array(
                'success' => true,
                'message' => 'noishee!',
                'errors'  => []
            )
        );
    }

    public static function do_ai_magic_parameters()
    {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the event'),
                'jsondata' => new external_value(PARAM_RAW, 'The data from form, encoded as a json array')
            )
        );
    }

    public static function do_ai_magic_is_allowed_from_ajax()
    {
        return true;
    }

    public static function do_ai_magic_returns()
    {
        return new external_value(PARAM_RAW, 'Update response');
    }

    /**
     * Undocumented function
     *
     * @param [type] $contextid
     * @param [type] $jsonformdata
     * @return void
     */
    public static function expand($contextid, $jsondata)
    {

        $params = self::validate_parameters(self::expand_parameters(), ['contextid' => $contextid, 'jsondata' => $jsondata]);
        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        self::validate_context($context);

        $serialiseddata = json_decode($params['jsondata']);

        $data = array();
        parse_str($serialiseddata, $data);

        return json_encode(
            array(
                'success' => true,
                'message' => 'noishee!',
                'errors'  => []
            )
        );
    }

    public static function expand_parameters()
    {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the event'),
                'jsondata' => new external_value(PARAM_RAW, 'The data from form, encoded as a json array')
            )
        );
    }

    public static function expand_is_allowed_from_ajax()
    {
        return true;
    }

    public static function expand_returns()
    {
        return new external_value(PARAM_RAW, 'Update response');
    }
}
