<?php
/**
 * The assignsubmission_pxaiwriter submission_updated event.
 *
 * @package    assignsubmission_pxaiwriter
 * @copyright  2023 Moxis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_pxaiwriter\event;

defined('MOODLE_INTERNAL') || die();


class submission_updated extends \mod_assign\event\submission_updated {

    /**
     * Init method.
     */
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assignsubmission_pxaiwriter';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $descriptionstring = "The user with id '$this->userid' updated a PX AI text submission with " .
            "course module id " .
            "'$this->contextinstanceid'";
        if (!empty($this->other['groupid'])) {
            $descriptionstring .= " for the group with id '{$this->other['groupid']}'";
        }

        return $descriptionstring . '.';
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        // No mapping available for 'assignsubmission_pxaiwriter'.
        return array('db' => 'assignsubmission_pxaiwriter', 'restore' => \core\event\base::NOT_MAPPED);
    }
}
