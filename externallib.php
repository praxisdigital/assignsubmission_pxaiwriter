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
        try {

            $params = self::validate_parameters(self::do_ai_magic_parameters(), ['contextid' => $contextid, 'jsondata' => $jsondata]);
            $context = context::instance_by_id($params['contextid'], MUST_EXIST);

            self::validate_context($context);

            $serialiseddata = json_decode($params['jsondata']);

            $data = array();
            parse_str($serialiseddata, $data);

            $payload = $config = $url = "";
            self::getOpenAIRequestConfig($payload, $config, $url);

            $payload['prompt'] =  $serialiseddata->text;

            $result = self::sendCurlRequest($url, $payload, "POST", $config);

            $result = self::jsonToObject($result);

            $genText = "";

            if (count($result->choices)) {
                $genText = $result->choices[0]->text;
            }

            return json_encode(
                array(
                    'success' => true,
                    'data' => $genText,
                    'message' => "Successful",
                    'errors'  => []
                )
            );
        } catch (Exception $ex) {
            return json_encode(
                array(
                    'success' => false,
                    'message' => "Failure :", $ex->getMessage(),
                    'errors'  => [$ex]
                )
            );
        }
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
        try {
            $params = self::validate_parameters(self::expand_parameters(), ['contextid' => $contextid, 'jsondata' => $jsondata]);
            $context = context::instance_by_id($params['contextid'], MUST_EXIST);

            self::validate_context($context);

            $serialiseddata = json_decode($params['jsondata']);

            $data = array();
            parse_str($serialiseddata, $data);

            self::getOpenAIRequestConfig($payload, $config, $url);

            $payload['prompt'] = "Expand on the following: " . $serialiseddata->text;

            $result = self::sendCurlRequest($url, $payload, "POST", $config);

            $result = self::jsonToObject($result);

            $genText = "";

            if (count($result->choices)) {
                $genText = $result->choices[0]->text;
            }

            return json_encode(
                array(
                    'success' => true,
                    'data' => $genText,
                    'message' => "Successful",
                    'errors'  => []
                )
            );
        } catch (Exception $ex) {
            return json_encode(
                array(
                    'success' => false,
                    'message' => "Failure :", $ex->getMessage(),
                    'errors'  => [$ex]
                )
            );
        }
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

    function getOpenAIRequestConfig(&$payload, &$config, &$url)
    {

        $adminConfig = self::getPluginAdminSettings();

        $config = [
            'Content-Type: ' . $adminConfig->content_type,
            'Authorization:' . $adminConfig->authorization,
            'Accept: ' . $adminConfig->content_type,
        ];

        $payload = array(
            "model" => $adminConfig->model,
            "prompt" => "",
            "temperature" => (float)$adminConfig->temperature,
            "max_tokens" =>  (int)$adminConfig->max_tokens,
            "top_p" => (float)$adminConfig->top_p,
            "frequency_penalty" => (float)$adminConfig->frequency_penalty,
            "presence_penalty" => (float)$adminConfig->presence_penalty
        );

        $url = $adminConfig->url;
    }

    function jsonToObject($json)
    {
        while (!is_object($json)) {
            $json = json_decode($json);
        }
        return $json;
    }

    function sendCurlRequest($endpoint, $data = [], $method = "GET", $headerConfig = array('Content-Type: application/json', 'Accept: application/json'))
    {

        try {

            $postdata = json_encode($data);

            $url = $endpoint;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerConfig);
            $result = curl_exec($ch);

            if ($result === false) {
                echo 'Curl error: ' . curl_error($ch);
            }

            curl_close($ch);
            return $result;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Summary : Gets the pxaiwriter admin settings from config_plugins table
     *              PLEASE RE-USE THIS FUNCTION!!!
     * Created By : Nilaksha
     * Created At : 05/01/2023
     *
     * @param [type] $setting
     * @return object
     */
    function getPluginAdminSettings($setting = "", $pluginName = 'assignsubmission_pxaiwriter')
    {

        // last_modified_by
        // api_key
        // presence_penalty
        // frequency_penalty
        // top_p
        // max_tokens
        // temperature
        // model
        // authorization
        // content_type
        // url
        // default
        // installrunning
        // version
        // granularity

        global $DB;
        if ($setting) {
            $dbparams = array(
                'plugin' => $pluginName,
                'name' => $setting
            );
            $result = $DB->get_record('config_plugins', $dbparams, '*', IGNORE_MISSING);

            if ($result) {
                return $result->value;
            }

            return false;
        }

        $dbparams = array(
            'plugin' => $pluginName,
        );
        $results = $DB->get_records('config_plugins', $dbparams);

        $config = new stdClass();
        if (is_array($results)) {
            foreach ($results as $setting) {
                $name = $setting->name;
                $config->$name = $setting->value;
            }
        }
        return $config;
    }
}
