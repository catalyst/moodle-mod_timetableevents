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

use mod_timetableevents\data_manager;

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

    $timetableevent = data_manager::override_values($timetableevent);

    $id = $DB->insert_record('timetableevents', $timetableevent);

    return $id;
}

/**
 * Update activity instance
 * @param stdClass $timetableevent Module instance
 * @return bool
 */
function timetableevents_update_instance(stdClass $timetableevent) : bool {
    global $DB;

    $timetableevent->timemodified = time();
    $timetableevent->id = $timetableevent->instance;

    $timetableevent = data_manager::override_values($timetableevent);

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
            return true;
        case
            FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case
            FEATURE_COMPLETION_HAS_RULES:
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
function timetableevents_extend_navigation_course(navigation_node $navigation, stdClass $course, context $context) : void {
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

/**
 * Display instance content.
 *
 * @param cm_info $cm
 */
function timetableevents_cm_info_view(cm_info $cm) {
    global $PAGE;
    $courseid = $cm->get_course()->id;
    $cminfo = get_fast_modinfo($courseid);
    $daterange = $cminfo->get_cm($cm->id)->customdata;

    $siteconfig = data_manager::get_site_config();
    $courseconfig = data_manager::get_course_config($courseid);

    $display = new \mod_timetableevents\output\display($courseid, $cm, $siteconfig, $courseconfig, $daterange);
    $renderer = $PAGE->get_renderer('mod_timetableevents');
    $cm->set_content($renderer->render($display), true);
}

/**
 * Sets dynamic information about a course module
 *
 * This function is called from cm_info when displaying the module
 *
 * @param cm_info $cm
 */
function timetableevents_cm_info_dynamic(cm_info $cm) {
        $cm->set_no_view_link();
        $cm->set_name("");
}

/**
 * Add a get_coursemodule_info function so we can cache the date ranges for each instance.
 *
 * @param stdClass $cm The coursemodule object.
 * @return cached_cm_info An object with cached cm info.
 */
function timetableevents_get_coursemodule_info(stdClass $cm): cached_cm_info {
    // This method is only called if the cache has been cleared or not been previously set,
    // so we can just calculate and store the date ranges.
    global $DB;

    // Get the module instance.
    $instance = $DB->get_record('timetableevents', ['id' => $cm->instance]);

    // Get the course config.
    $courseconfig = \mod_timetableevents\data_manager::get_course_config($cm->course);
    $info = new cached_cm_info();
    $dateranges = [];

    if (isset($instance->courseoverride) && $instance->courseoverride != null) {
        $othergroups = data_manager::get_other_course_groups($instance->courseoverride);
    }

    // Create date range cache for all groups.
    $groups = groups_get_all_groups($cm->course);
    if (isset($othergroups)) {
        $groups = array_merge($groups, $othergroups);
    }
    foreach ($groups as $group) {
        $dateranges[$group->id] = data_manager::calculate_date_range($cm, $instance, $courseconfig, $group->id);
    }

    // Create a default date range for users in no groups and course events.
    $group = 0;
    $dateranges[0] = data_manager::calculate_date_range($cm, $instance, $courseconfig, $group);
    $info->customdata = $dateranges;
    return $info;

}
