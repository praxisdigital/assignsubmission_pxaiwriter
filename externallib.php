<?php

use assignsubmission_pxaiwriter\app\factory;

require_once($CFG->libdir . "/phpspreadsheet/vendor/autoload.php");
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/webservice/externallib.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');

class mod_assign_submission_pxaiwriter_external extends external_api
{

    /**
     * Calls the Open AI end point with the raw instruction that was passed by a student
     *
     * @param [type] $contextid
     * @param [type] $jsonformdata
     * @return false|string
     */
    public static function do_ai_magic($jsondata, $contextid = 1)
    {
        global $USER, $DB;
        try {

            $params = self::validate_parameters(self::do_ai_magic_parameters(), ['contextid' => $contextid, 'jsondata' => $jsondata]);
            $context = context::instance_by_id($params['contextid'], MUST_EXIST);

            self::validate_context($context);

            $serialiseddata = json_decode($params['jsondata']);

            $assignmentid = intval($serialiseddata->assignmentid);
            $userid = intval($USER->id);
            $date = strtotime("today");

            $attempt_record = self::getAIAttemptRecord($assignmentid, $userid);

            if (false) {
                return json_encode(
                    array(
                        'success' => false,
                        'message' => "Failure : " . get_string('ai_attempt_exceed_msg', 'assignsubmission_pxaiwriter'),
                        'errors'  => ["max_attempt_exceed_error"]
                    )
                );
            }

            if (!$attempt_record) {
                $attempt_record = new stdClass();
                $attempt_record->assignment = $assignmentid;
                $attempt_record->userid = $userid;
            }

            $payload = $config = $url = "";
            self::getOpenAIRequestConfig($payload, $config, $url);

            $payload['prompt'] =  $serialiseddata->text;

            $result = self::sendCurlRequest($url, $payload, "POST", $config);

            if ($result === false) {
                throw new Exception("An error occurred while calling the API.");
            }

            $result = self::jsonToObject($result);

            if ($result === false) {
                throw new Exception("An error occurred while parsing the API request.");
            }

            $genText = "";

            if (!empty($result->choices)) {
                $genText = $result->choices[0]->text;
            }

            self::updateAttemptsHistory($attempt_record);

            $msg = self::getAvailableAIattemtsMessage($attempt_record);

            if (!empty($genText)) {
                $entity = factory::make()->ai()->history()->create_entity_by_current_user(
                    1,
                    1,
                    $genText
                );
                factory::make()->ai()->history()->repository()->insert($entity);
            }

            return json_encode(
                array(
                    'success' => true,
                    'data' => $genText,
                    'attempt_text' => $msg,
                    'message' => "Successful",
                    // "o_data" => $result,
                    'errors'  => []
                )
            );
        } catch (Exception $ex) {
            return json_encode(
                array(
                    'success' => false,
                    'message' => "Failure : " . $ex->getMessage(),
                    'errors'  => [$ex]
                )
            );
        }
    }

    /**
     * Gets how many attempts out of max api attemts left made by a student for an assignment
     *
     * @param [type] $attemptRecord
     * @return lang_string|string
     */
    public static function getAvailableAIattemtsMessage($attemptRecord)
    {
        $usedApiAttempts = 0;
        $maxAttempts = self::getPluginAdminSettings('attempt_count');

        if ($attemptRecord) {
            $usedApiAttempts = ($attemptRecord->api_attempts) ? $attemptRecord->api_attempts : 0;
        }

        $str = new stdClass();
        $str->remaining = ($maxAttempts - $usedApiAttempts);
        $str->maximum = $maxAttempts;
        return get_string('remaining_ai_attempt_count_text', 'assignsubmission_pxaiwriter', $str);
    }

    /**
     * Retrieves the AI request attempt record by the current date, the assignment id and the user id
     *
     * @param $assignmentid
     * @param $userid
     * @return false|object
     * @throws dml_exception
     */
    public static function getAIAttemptRecord($assignmentid, $userid)
    {
        global $DB;
        return $DB->get_record('pxaiwriter_api_attempts', array('assignment' => $assignmentid, 'userid' => $userid, 'api_attempt_date' => strtotime("today")));
    }

    public static function do_ai_magic_parameters()
    {
        return new external_function_parameters(
            [
                'jsondata' => new external_value(PARAM_RAW, 'The data from form, encoded as a json array'),
                'contextid' => new external_value(PARAM_INT, 'The context id for the event', VALUE_DEFAULT, 1),
            ]
        );
    }

    public static function do_ai_magic_returns()
    {
        return new external_value(PARAM_RAW, 'Update response');
    }

    /**
     * Calls the Open API to expand a given text content by a student. This prepends expand instruction to the request
     *
     * @param [type] $contextid
     * @param [type] $jsonformdata
     * @return false|string
     */
    public static function expand($contextid, $jsondata)
    {
        global $USER, $DB;
        try {
            $params = self::validate_parameters(self::expand_parameters(), ['contextid' => $contextid, 'jsondata' => $jsondata]);
            $context = context::instance_by_id($params['contextid'], MUST_EXIST);

            self::validate_context($context);

            $serialiseddata = json_decode($params['jsondata']);

            $assignmentid = intval($serialiseddata->assignmentid);
            $userid = intval($USER->id);

            $attempt_record = $DB->get_record('pxaiwriter_api_attempts', array('assignment' => $assignmentid, 'userid' => $userid, 'api_attempt_date' => strtotime("today")));

            if (self::isExceedingAttemptCount($attempt_record)) {
                return json_encode(
                    array(
                        'success' => false,
                        'message' => "Failure : " . get_string('ai_attempt_exceed_msg', 'assignsubmission_pxaiwriter'),
                        'errors'  => ["max_attempt_exceed_error"]
                    )
                );
            }

            if (!$attempt_record) {
                $attempt_record = new stdClass();
                $attempt_record->assignment = $assignmentid;
                $attempt_record->userid = $userid;
            }

            self::getOpenAIRequestConfig($payload, $config, $url);

            $payload['prompt'] = get_string('expand_command', 'assignsubmission_pxaiwriter') . " : " . $serialiseddata->text;

            $result = self::sendCurlRequest($url, $payload, "POST", $config);

            if ($result === false) {
                throw new Exception("An error occured while calling the API.");
            }

            $result = self::jsonToObject($result);

            if ($result === false) {
                throw new Exception("An error occured while parsing the API request.");
            }

            $genText = "";

            if (count($result->choices)) {
                $genText = $result->choices[0]->text;
            }

            self::updateAttemptsHistory($attempt_record);

            $msg = self::getAvailableAIattemtsMessage($attempt_record);

            return json_encode(
                array(
                    'success' => true,
                    'data' => $genText,
                    'attempt_text' => $msg,
                    'message' => "Successful",
                    // "o_data" => $result,
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

    /**
     * Helper function to get the Open AI API request  payload and header configuration formatted 
     *
     * @param [type] $payload
     * @param [type] $config
     * @param [type] $url
     * @return void
     */
    static function getOpenAIRequestConfig(&$payload, &$config, &$url)
    {

        $adminConfig = self::getPluginAdminSettings();

        $config = [
            'Content-Type: application/json',
            'Authorization:' . $adminConfig->authorization,
            'Accept: application/json',
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

    /**
     * Helper function to convert a json string to an object recursively 
     *
     * @param [type] $json
     * @return object
     */
    static function jsonToObject($json)
    {
        $i = 0;
        while (!is_object($json)) {
            if ($i > 3) {
                $json = false;
                break;
            }
            $json = json_decode($json);
            $i++;
        }
        return $json;
    }

    /**
     * Helper function to send a custom CURL
     *
     * @param [type] $endpoint
     * @param array $data
     * @param string $method
     * @param array $headerConfig
     * @return string
     */
    static function sendCurlRequest(
        $endpoint,
        $data = [],
        $method = "GET",
        $headerConfig = ['Content-Type: application/json', 'Accept: application/json']
    )
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
                mtrace('Curl error: ' . curl_error($ch));
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
     * @param string $setting
     * @param string $pluginName
     * @return mixed
     * @throws dml_exception
     */
    static function getPluginAdminSettings($setting = "", $pluginName = 'assignsubmission_pxaiwriter')
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
        if (empty($setting)) {
            return get_config($pluginName);
        }
        return get_config($pluginName, $setting);
    }

    /**
     * Updates student AI attempt history for assignments
     *
     * @param [type] $attempt_record
     * @return void
     * @throws dml_exception
     */
    static function updateAttemptsHistory($attempt_record)
    {
        global $DB;

        if ($attempt_record->id) {
            $attempt_record->api_attempts = $attempt_record->api_attempts + 1;
            $DB->update_record('pxaiwriter_api_attempts', $attempt_record);
        } else {
            $attempt_record->api_attempt_date = strtotime("today");
            $attempt_record->api_attempts = 1;
            $DB->insert_record('pxaiwriter_api_attempts', $attempt_record);
        }
    }

    /**
     * Checks if an attempt record exceeds the allowed API request count
     *
     * @param [type] $attempt_record
     * @return boolean
     */
    static function isExceedingAttemptCount($attempt_record)
    {
        $maxattempts = self::getPluginAdminSettings('attempt_count');
        return !($attempt_record->api_attempts < $maxattempts);
    }
}
