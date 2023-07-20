<?php
/**
 * The assignsubmission_pxaiwriter assessable uploaded event.
 *
 * @package    assignsubmission_pxaiwriter
 * @copyright  2023 Moxis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_pxaiwriter\event;

defined('MOODLE_INTERNAL') || die();

class assessable_uploaded extends \core\event\assessable_uploaded {

    /**
     * Legacy event files.
     *
     * @var array
     */
    protected $legacyfiles = array();

    public function get_description() {
        return "The user with id '$this->userid' has saved an ai writer text submission with id '$this->objectid' " .
            "in the assignment activity with course module id '$this->contextinstanceid'.";
    }

    public static function get_name() {
        return get_string('eventassessableuploaded', 'assignsubmission_pxaiwriter');
    }

    public function get_url() {
        return new \moodle_url('/mod/assign/view.php', array('id' => $this->contextinstanceid));
    }

    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assign_submission';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_submission', 'restore' => 'submission');
    }
}
