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
require_once($CFG->libdir . '/adminlib.php');

// No guest autologin.
require_login(0, false);

admin_externalpage_setup('managemodules');

$pageurl = new moodle_url('/mod/timetableevents/delete.php');
$PAGE->set_url($pageurl);
$PAGE->set_title($SITE->shortname . ': '  .  get_string('pluginname', 'timetableevents'));

$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', null, PARAM_INT);

if (!is_null($delete)) {
    require_sesskey();
    global $DB;

    $DB->delete_records_list('timetableevents_term', 'yearid', [$id]);
    $DB->delete_records_list('timetableevents_year', 'id', [$id]);

    redirect(new moodle_url('/admin/settings.php', ['section' => 'modsettingtimetableevents']));
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginsettings:acadyears:edit', 'mod_timetableevents'));
    echo $OUTPUT->box_start('generalbox');

    $confirmurl = new moodle_url($pageurl, ['id' => $id, 'delete' => $delete]);

    echo $OUTPUT->confirm('Are you sure?', $confirmurl, $pageurl);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
}
