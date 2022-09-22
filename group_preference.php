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

// No guest autologin.
require_login(0, false);

$course = required_param('course', PARAM_INT);
$group = required_param('mod_timetableevents-select-groups', PARAM_INT);
$sesskey = required_param('sesskey', PARAM_ALPHANUM);

if (!is_null($course)) {
    require_sesskey();
    set_user_preference('mod_timetableevents_' . $course , $group);
    redirect(new moodle_url('/course/view.php', array('id' => $course)));
}

