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
 * Index page for mod_timetableevents
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);   // Course.

$PAGE->set_url('/mod/timetableevents/index.php', ['id' => $id]);

if (!empty($id)) {
    if (!$course = $DB->get_record('course', ['id' => $id])) {
        moodle_exception('invalidcourseid');
    }
} else {
    moodle_exception('missingparameter');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

$modulename = get_string('modulename', 'timetableevents');
$modulenameplural = get_string("modulenameplural", 'timetableevents');
$strname = get_string('name');
$strsummary = get_string("summary");
$strsection = get_string("section", 'timetableevents');
$strlastmodified = get_string("lastmodified");

$PAGE->set_title($modulename);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($modulename);
echo $OUTPUT->header();
echo $OUTPUT->heading($modulename);

$usesections = course_format_uses_sections($course->format);

if ($usesections) {
    $sortorder = "cw.section ASC";
} else {
    $sortorder = "m.timemodified DESC";
}

if (! $timetableevents = get_all_instances_in_course("timetableevents", $course)) {
    notice(get_string('thereareno', 'moodle', $modulename), "../../course/view.php?id=$course->id");
    exit;
}

$table = new html_table();

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_' . $course->format);
    $table->head  = [$strsectionname, $strname, $strsummary, $strsection];
    $table->align = ["center", "left", "left", "left"];
} else {
    $table->head  = [$strlastmodified, $strname, $strsummary, $strsection];
    $table->align = ["left", "left", "left", "left"];
}

foreach ($timetableevents as $timetableevent) {
    $context = context_module::instance($timetableevent->coursemodule);
    $tt = "";
    if ($usesections) {
        if ($timetableevent->section) {
            $tt = get_section_name($course, $timetableevent->section);
        }
    } else {
        $tt = userdate($timetableevent->timemodified);
    }
    $report = '&nbsp;';
    $reportshow = '&nbsp;';

    $options = (object)['noclean' => true];
    if (!$timetableevent->visible) {
        // Show dimmed if the mod is hidden.
        $table->data[] = [
            $tt,
            html_writer::link(
                'view.php?id=' . $timetableevent->coursemodule,
                format_string($timetableevent->name),
                ['class' => 'dimmed']
            ),
            format_module_intro('timetableevents', $timetableevent, $timetableevent->coursemodule),
            $reportshow,
        ];
    } else {
        // Show normal if the mod is visible.
        $table->data[] = [$tt, html_writer::link(
            'view.php?id=' . $timetableevent->coursemodule,
            format_string($timetableevent->name)
        ),
            format_module_intro('timetableevents', $timetableevent, $timetableevent->coursemodule), $reportshow, ];
    }
}

echo html_writer::empty_tag('br');

echo html_writer::table($table);

echo $OUTPUT->footer();
