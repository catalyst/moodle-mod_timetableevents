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
use mod_timetableevents\forms\acadyears_settings;

// No guest autologin.
require_login(0, false);

admin_externalpage_setup('managemodules');

$pageurl = new moodle_url('/mod/timetableevents/acadyears.php');
$PAGE->set_url($pageurl);

$PAGE->set_title($SITE->shortname . ': '  . get_string('pluginname', 'timetableevents'));

$id = optional_param('id', null, PARAM_INT);
$yearid = optional_param('yearid', null, PARAM_INT);
$edit = optional_param('edit', null, PARAM_INT);
$delete = optional_param('delete', null, PARAM_INT);

$customdata = null;

if (isset($id)) {
    $customdata['id'] = $id;
}

if (!is_null($edit)) {
    $customdata['edit'] = $edit;
}

$mform = new acadyears_settings($pageurl, $customdata, null, null);

if ($data = $mform->get_data()) {

    global $DB;

    if (isset($data->edit) && $data->edit == 1) {

        data_manager::update_academic_terms($data);

    } else {

        $setdefaults = false;
        if (!$DB->record_exists('timetableevents_year', [])) {
            $setdefaults = true;
        }
        $yearid = data_manager::create_academic_year($data->name);
        data_manager::create_academic_terms($data, $yearid);

        if ($setdefaults) {
            set_config('currentacadyear', $yearid, 'mod_timetableevents');
            set_config('teachingstartdate', $data->startdate[0], 'mod_timetableevents');
            set_config('firstteachingsection', 5, 'mod_timetableevents');
            set_config('teachinginterval', \mod_timetableevents\teaching_intervals::FORTNIGHTLY, 'mod_timetableevents');
        }
    }

    redirect(new moodle_url('/admin/settings.php', array('section' => 'modsettingtimetableevents')));

} else if ($mform->is_cancelled()) {

    redirect(new moodle_url('/admin/settings.php', array('section' => 'modsettingtimetableevents')));

} else {

    if (!is_null($edit) && $edit == 1) {
        if (!is_null($yearid)) {
            $id = $yearid;
        }
        $acadyear = data_manager::get_terms($id);
        $acadyear = $mform->data_preprocessing($acadyear[$id]);
        $mform->set_data($acadyear);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginsettings:acadyears:edit', 'mod_timetableevents'));
echo $OUTPUT->box_start('generalbox');

$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
