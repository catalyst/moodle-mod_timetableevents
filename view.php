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
 * Timetable events view.php
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);    // Course Module ID.
$t = optional_param('t', 0, PARAM_INT);     // Timetable event ID.

if ($id) {
    $PAGE->set_url('/mod/timetableevents/index.php', array('id' => $id));
    if (!$cm = get_coursemodule_from_id('timetableevents', $id)) {
        moodle_exception('invalidcoursemodule'); // NOTE this is invalid use of print_error, must be a lang string id.
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        moodle_exception('coursemisconf');  // NOTE As above.
    }
    if (!$timetableevent = $DB->get_record('timetableevents', array('id' => $cm->instance))) {
        moodle_exception('invalidcoursemodule'); // NOTE As above.
    }

} else {
    $PAGE->set_url('/mod/timetableevents/index.php', array('t' => $t));
    if (!$timetableevent = $DB->get_record("timetableevents", array("id" => $t))) {
        moodle_exception('invalidcoursemodule');
    }
    if (!$course = $DB->get_record("course", array("id" => $timetableevent->course)) ) {
        moodle_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance("timetableevents", $timetableevent->id, $course->id, true)) {
        moodle_exception('invalidcoursemodule');
    }
}

require_login($course, true, $cm);

$url = course_get_url($course, null, []);
$url->set_anchor('module-' . $id);
redirect($url);
