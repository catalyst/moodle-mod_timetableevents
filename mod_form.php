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
 * Timetable events mod_form.php
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/timetableevents/lib.php');

/**
 * Class for the standard module settings page.
 *
 * @package    mod_timetableevents
 * @copyright  2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_timetableevents_mod_form extends moodleform_mod {

    /**
     * Form definition.
     */
    public function definition() {
        global $PAGE;

        $PAGE->force_settings_menu();

        $mform =& $this->_form;

        // Defaults toggle.
        $mform->addElement('checkbox', 'coursedefaults', get_string('modsetting:coursedefaults', 'timetableevents'));
        $mform->setDefault('coursedefaults', 1);

        // Course selector.
        $courses = get_courses("all", "c.fullname ASC");
        $courseoptions = [];
        foreach ($courses as $course) {
            $courseoptions[$course->id] = $course->fullname;
        }
        $options = array(
            'multiple' => false,
            'showsuggestions' => true,
            'placeholder' => get_string('modsetting:placeholder', 'timetableevents'),
        );
        $mform->addElement('autocomplete', 'courseoverride',
            get_string('modsetting:coursesearch', 'timetableevents'), $courseoptions, $options);
        $mform->setDefault('courseoverride', $this->get_course()->id);
        $mform->hideIf('courseoverride', 'coursedefaults', 'checked');

        // Group selector.
        $groups = groups_get_course_data($this->get_course()->id);
        $groupoptions = [];

        if (count($groups->groups) > 0) {
            $groupoptions[] = 'No group selected';
            foreach ($groups->groups as $group) {
                $groupoptions[$group->id] = $group->name;
            }

            $mform->addElement('select', 'groupid',
                get_string('modsetting:groupsearch', 'timetableevents'), $groupoptions, $options);
            $mform->hideIf('groupid', 'coursedefaults', 'checked');

        } else {
            // Static elements can only be hidden using hideIf is added to a group.
            $group = [];
            $group[] =& $mform->createElement('static', 'groupid', get_string('modsetting:groupsearch', 'timetableevents'),
                get_string('modsetting:nogroups', 'timetableevents'));
            $mform->addGroup($group, 'groupgroup', '', ' ', false);
            $mform->hideIf('groupgroup', 'coursedefaults', 'checked');
        }

        $mform->addElement('date_selector', 'startdate', get_string('modsetting:daterange', 'timetableevents'));
        $mform->hideIf('startdate', 'coursedefaults', 'checked');

        $mform->addElement('date_selector', 'enddate', '');
        $mform->hideIf('enddate', 'coursedefaults', 'checked');

        $mform->addElement('hidden', 'showdescription', 1);
        $mform->setType('showdescription', PARAM_INT);

        $this->standard_coursemodule_elements();
        $mform->setExpanded('modstandardelshdr', false);

        $this->add_action_buttons();
    }

    /**
     * Common module settings data.
     */
    public function definition_after_data() {

        global $COURSE;

        $mform =& $this->_form;

        if (isset($this->current->courseoverride) && $this->current->courseoverride != null) {
            $mform->setDefault('coursedefaults', 0);
        }

        if ($COURSE->groupmodeforce) {
            if ($mform->elementExists('groupmode')) {
                // The groupmode can not be changed if forced from course settings.
                $mform->hardFreeze('groupmode');
            }
        } else {
            if (isset($this->_cm->groupmode) && $this->_cm->groupmode != VISIBLEGROUPS) {
                $mform->setDefault('groupmode', $this->_cm->groupmode);
            } else {
                $mform->setDefault('groupmode', VISIBLEGROUPS);
            }
        }
    }
}
