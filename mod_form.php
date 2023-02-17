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

use mod_timetableevents\data_manager;

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
        $mform->addHelpButton('coursedefaults', 'modsetting:coursedefaults', 'timetableevents');

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

        $mform->addElement('course', 'courseoverride', get_string('modsetting:coursesearch', 'timetableevents'), $options);
        $mform->setDefault('courseoverride', $this->get_course()->id);
        $mform->hideIf('courseoverride', 'coursedefaults', 'checked');
        $mform->addHelpButton('courseoverride', 'modsetting:coursesearch', 'timetableevents');

        // Group selector.
        $courseid = $this->get_course()->id;
        $groups = groups_get_course_data($courseid);
        $groupoptions[] = get_string('nogroupselected', 'timetableevents');

        if (count($groups->groups) > 0) {
            foreach ($groups->groups as $group) {
                $groupoptions[$group->id] = $group->name;
            }
        } else {
            $groupoptions = [get_string('modsetting:nogroups', 'timetableevents')];
            $mform->addElement('hidden', 'nogroups', 1);
            $mform->setType('nogroups', PARAM_INT);
        }

        $mform->addElement('select', 'groupid',
            get_string('modsetting:groupsearch', 'timetableevents'), $groupoptions, $options);
        $mform->hideIf('groupid', 'coursedefaults', 'checked');
        $mform->disabledIf('groupid', 'nogroups', 'eq',  1);
        $mform->addHelpButton('groupid', 'modsetting:groupsearch', 'timetableevents');

        $mform->addElement('date_selector', 'startdate',
            get_string('modsetting:daterange', 'timetableevents'), ['optional' => true]);
        $mform->hideIf('startdate', 'coursedefaults', 'checked');
        $mform->addHelpButton('startdate', 'modsetting:daterange', 'timetableevents');

        $mform->addElement('date_selector', 'enddate', '');
        $mform->hideIf('enddate', 'coursedefaults', 'checked');
        $mform->disabledIf('enddate', 'startdate[enabled]', 'notchecked');

        $mform->addElement('hidden', 'showdescription', 1);
        $mform->setType('showdescription', PARAM_INT);

        $this->standard_coursemodule_elements();
        $this->standard_intro_elements();
        $mform->setExpanded('modstandardelshdr', false);

        $this->add_action_buttons(true, false, null);

        $PAGE->requires->js_call_amd('mod_timetableevents/form', 'instance');
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

        $mform->setDefault('startdate[enabled]', 0);

        $groupid = $mform->getElementValue('groupid');

        if (!is_null($groupid)) {
            $courseid = $mform->getElementValue('courseoverride');
            $groups = data_manager::get_other_course_groups($courseid[0]);
            if (count($groups) > 0) {
                $groupoptions[] = get_string('nogroupselected', 'timetableevents');
                foreach ($groups as $group) {
                    $groupoptions[$group->id] = $group->name;
                }
                $groupidel = $mform->getElement('groupid');
                $groupidel->removeOptions();
                $groupidel->load($groupoptions);
                $mform->setDefault('groupid', $groupid[0]);
            } else {
                // Static elements can only be hidden using hideIf is added to a group.
                $group = [];
                $group[] =& $mform->createElement('static', 'groupid', get_string('modsetting:groupsearch', 'timetableevents'),
                    get_string('modsetting:nogroups', 'timetableevents'));
                $mform->addGroup($group, 'groupgroup', '', ' ', false);
                $mform->hideIf('groupgroup', 'coursedefaults', 'checked');
            }
        }

    }

    // phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * Allows modules to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param array $default_values passed by reference
     * @return array
     */
    public function data_preprocessing(&$default_values) {

        // Add course settings based on site config if they're not already set.
        data_manager::set_course_defaults($default_values);

        return $default_values;
    }
    // phpcs:enable

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        // If it's a new cm with a group override and the group is in this course,
        // add an initial group availability restriction. Teaching staff can manage any further
        // restriction updates themselves.
        if (!empty($data->groupid) && $data->coursemodule == 0 &&
            ($data->courseoverride == null || $data->courseoverride == $data->course)) {
            data_manager::update_availability($data);
        }

        // Set end date to zero if startdate is also zero.
        if ($data->startdate == 0) {
            $data->enddate = 0;
        }
    }
}
