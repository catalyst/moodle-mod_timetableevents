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
 * Timetable events lib.php
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add activity instance
 * @param stdClass $timetableevent Module instance
 * @return int $id Instance ID
 */
function timetableevents_add_instance(stdClass $timetableevent) : int {

    global $DB, $USER;

    $timetableevent->usermodified = $USER->id;
    $timetableevent->timemodified = time();
    if ($timetableevent->update == 0) {
        $timetableevent->timecreated = time();
    }

    $timetableevent = \mod_timetableevents\data_manager::override_values($timetableevent);

    $id = $DB->insert_record('timetableevents', $timetableevent);

    return $id;
}

/**
 * Add activity instance
 * @param stdClass $timetableevent Module instance
 * @return bool
 */
function timetableevents_update_instance(stdClass $timetableevent) : bool {
    global $DB;

    $timetableevent->timemodified = time();
    $timetableevent->id = $timetableevent->instance;
    if (!isset($timetableevent->groupid)) {
        $timetableevent->groupid = null;
    }

    $timetableevent = \mod_timetableevents\data_manager::override_values($timetableevent);

    $completiontimeexpected = !empty($timetableevent->completionexpected) ? $timetableevent->completionexpected : null;
    \core_completion\api::update_completion_date_event(
        $timetableevent->coursemodule, 'label', $timetableevent->id, $completiontimeexpected);

    return $DB->update_record("timetableevents", $timetableevent);
}

/**
 * Delete activity instance
 * @param int $id Module instance ID
 * @return bool
 */
function timetableevents_delete_instance(int $id) : bool {
    global $DB;
    $DB->delete_records('timetableevents', array('id' => $id));
    return true;
}

/**
 * Set the course settings the plugin supports.
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function timetableevents_supports(string $feature) : ?bool {
    switch($feature) {
        case
            FEATURE_IDNUMBER:
            return true;
        case
            FEATURE_GROUPS:
            return true;
        case
            FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return false;
        case
            FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case
            FEATURE_GRADE_HAS_GRADE:
            return false;
        case
            FEATURE_GRADE_OUTCOMES:
            return false;
        case
            FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case
            FEATURE_BACKUP_MOODLE2:
            return true;
        case
            FEATURE_NO_VIEW_LINK:
            return true;

        default:
            return null;
    }
}

/**
 * Extend the navigation to show a link in the course admin settings menu.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course     The course to object for the tool
 * @param context         $context    The context of the course
 * @return void
 */
function mod_timetableevents_extend_navigation_course(navigation_node $navigation, stdClass $course, context $context) : void {
    if (has_capability('moodle/course:manageactivities', $context)) {
        $url = new moodle_url('/mod/timetableevents/course.php', array('id' => $course->id));
        $navigation->add(
            get_string('pluginname', 'mod_timetableevents'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('icon', '', 'mod_timetableevents')
        );
    }
}
