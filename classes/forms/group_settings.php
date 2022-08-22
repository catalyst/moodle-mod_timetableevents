<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form class for group settings.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\forms;

use mod_timetableevents\data_manager;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');
require_once($CFG->dirroot.'/lib/grouplib.php');
require_once($CFG->dirroot.'/mod/timetableevents/lib.php');

/**
 * Class for the group overrides settings page.
 *
 * @package    mod_timetableevents
 * @copyright  2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_settings extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {

        $mform =& $this->_form;

        $groups = data_manager::get_groups($this->_customdata['course']->id);
        if (count($groups) > 0) {
            $groupoptions = [];
            foreach ($groups as $group) {
                $groupoptions[$group->id] = $group->name;
            }
            $mform->addElement('select', 'groupid',
                get_string('coursesetting:groupoverrides', 'timetableevents'), $groupoptions);

            $terms = data_manager::get_terms($this->_customdata['year']);
            if ($terms) {
                $termoptions = [];
                foreach ($terms[$this->_customdata['year']] as $term) {
                    $termoptions[$term->termid] = $term->termname;
                }

                $mform->addElement('select', 'startingtermid', get_string('coursesetting:term', 'timetableevents'), $termoptions);
            } else {
                $mform->addElement('static', 'academicyear', get_string('coursesetting:academicyear', 'timetableevents'),
                    get_string('coursesetting:academicyear:notconfigured', 'timetableevents'));
            }

            $mform->addElement('date_selector', 'teachingstartdate',
                get_string('coursesetting:teachingstartdate', 'timetableevents'));

            // Reset keys so we can set the date from the first element in the terms array.
            $termoptions = array_keys($termoptions);
            $mform->setDefault('teachingstartdate', $terms[$this->_customdata['year']][$termoptions[0]]->startdate);

            $this->add_action_buttons();
        } else {
            $mform->addElement('static', 'groupid', get_string('coursesetting:groupoverrides', 'timetableevents'),
                get_string('coursesetting:groupoverride:notconfigured', 'timetableevents'));
            $mform->addElement('cancel', 'cancel', get_string('cancel'));
        }

        $mform->addElement('hidden', 'id', $this->_customdata['course']->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'year', $this->_customdata['year']);
        $mform->setType('year', PARAM_INT);

        $mform->addElement('hidden', 'termsjson', json_encode((object) $terms));
        $mform->setType('termsjson', PARAM_RAW);

    }
}
