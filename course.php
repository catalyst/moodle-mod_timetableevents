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

use mod_timetableevents\data_manager;
use mod_timetableevents\forms\course_settings;

$course = optional_param('id', null, PARAM_INT);
$course = get_course($course);

global $DB, $USER, $PAGE;
// No guest autologin.
require_login($course, false);
$context = \context_course::instance($course->id);
require_capability('mod/timetableevents:addinstance', $context, $USER->id);

$PAGE->set_pagelayout('incourse');
$pageurl = new moodle_url('/mod/timetableevents/course.php', ['id' => $course->id]);
$PAGE->set_url($pageurl);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php', array('id' => $course->id)));
$PAGE->navbar->add(get_string('courseadministration'), new moodle_url('/course/admin.php', array('courseid' => $course->id)));
$PAGE->navbar->add(get_string('pluginname', 'timetableevents'));

$PAGE->set_title($SITE->shortname . ': '  .  get_string('pluginname', 'timetableevents'));

$saveddata = data_manager::get_course_form_data($course->id);
$mform = new course_settings($pageurl, array('data' => $saveddata, 'course' => $course), null, null);

if ($data = $mform->get_data()) {

    $courserecord = $DB->get_record('timetableevents_course', ['courseid' => $course->id]);

    // Create record or update timetableevents_course table.
    $courseobj = new stdClass();
    $courseobj->courseid = $course->id;
    $courseobj->startingtermid = $data->term;
    $courseobj->teachingstartdate = $data->teachingstartdate;
    $courseobj->firstsection = $data->firstsection;
    $courseobj->teachinginverval = $data->teachinginverval;
    $courseobj->footertext = $data->footertext;

    if ($courserecord) {
        $courseobj->id = $courserecord->id;
        $result = $DB->update_record('timetableevents_course', $courseobj);
    } else {
        $id = $DB->insert_record('timetableevents_course', $courseobj);
    }

    // Create record or update timetableevents_section table.
    $data->course = $course->id;
    $sectionobjs = data_manager::create_section_objects($data);
    foreach ($sectionobjs as $sectionobj) {
        $section = $DB->get_record('timetableevents_section', ['sectionid' => $sectionobj->sectionid]);
        if (!$section) {
            $result = $DB->insert_record('timetableevents_section', $sectionobj);
        } else {
            $sectionobj->id = $section->id;
            $result = $DB->update_record('timetableevents_section', $sectionobj);
        }
    }

    // Remove group overrides.
    if ($data->removeoverrides != "") {
        $overrides = ltrim($data->removeoverrides, ',');
        $overrides = explode(',', $overrides);
        foreach ($overrides as $override) {
            $DB->delete_records('timetableevents_group', ['id' => $override]);
        }
    }

    // If updating the course settings, rebuild the course cache.
    rebuild_course_cache($course->id);

    redirect(new moodle_url('/course/admin.php', array('courseid' => $course->id)));

} else if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/admin.php', array('courseid' => $course->id)));
}

if ($saveddata) {
    $mform->set_data($saveddata);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursesetting:settings', 'timetableevents'));
echo $OUTPUT->box_start('generalbox');

$mform->display();
echo $OUTPUT->box_end();
$params = [
    'course' => $course->id,
    'sesskey' => sesskey()
];
$PAGE->requires->js_call_amd('mod_timetableevents/form', 'course', $params);
echo $OUTPUT->footer();
