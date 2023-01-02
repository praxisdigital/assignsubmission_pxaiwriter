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
    public static function do_ai_magic($contextid, $jsonformdata)
    {
        global $DB, $CFG, $USER;
    }

    public static function expand($contextid, $jsonformdata)
    {
    }
}
