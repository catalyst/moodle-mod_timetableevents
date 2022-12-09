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
 * Course administration settings.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

use mod_timetableevents\forms\group_settings;

$course = required_param('id', PARAM_INT);
$year = required_param('year', PARAM_INT);
$course = get_course($course);

require_sesskey();

global $DB, $USER, $PAGE;

// No guest autologin.
require_login($course, false);
$context = \context_course::instance($course->id);
require_capability('mod/timetableevents:addinstance', $context, $USER->id, true, $errormessage = 'nopermissions');

$pageurl = new moodle_url('/mod/timetableevents/group.php');
$PAGE->set_url($pageurl);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php', array('id' => $course->id)));
$PAGE->navbar->add(get_string('courseadministration'), new moodle_url('/course/admin.php', array('courseid' => $course->id)));
$PAGE->navbar->add(get_string('pluginname', 'timetableevents'));

$PAGE->set_title($SITE->shortname . ': '  .  get_string('pluginname', 'timetableevents'));

$mform = new group_settings($pageurl, array('year' => $year, 'course' => $course), null, null);

if ($data = $mform->get_data()) {

    $group = $DB->get_record('timetableevents_group', ['groupid' => $data->groupid]);

    $groupobj = new stdClass();
    $groupobj->groupid = $data->groupid;
    $groupobj->startingtermid = $data->startingtermid;
    $groupobj->teachingstartdate = $data->teachingstartdate;

    if (!$DB->get_record('timetableevents_group', ['groupid' => $data->groupid ])) {
        $id = $DB->insert_record('timetableevents_group', $groupobj);
    } else {
        $groupobj->id = $group->id;
        $DB->update_record('timetableevents_group', $groupobj);
    }

    redirect(new moodle_url('/mod/timetableevents/course.php', array('id' => $course->id)));

} else if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/timetableevents/course.php', array('id' => $course->id)));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursesetting:settings', 'timetableevents'));
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();

$PAGE->requires->js_call_amd('mod_timetableevents/form', 'group');
echo $OUTPUT->footer();
