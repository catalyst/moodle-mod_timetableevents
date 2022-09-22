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
 * Define web service functions for mod_timetableevents
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = [
    'timetableventsservice' => [
        'functions' => [
            'mod_timetableevents_import_events'
        ],
        'requiredcapability' => 'mod/timetableevents:import',
        'restrictedusers' => 0,
        'enabled' => 1,
    ]
];

$functions = [
    'mod_timetableevents_import_events' => [
        'classname' => 'mod_timetableevents\\external\\import_events',
        'methodname' => 'execute',
        'description' => 'Import new or updated timetable events',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'mod/timetableevents:import'
    ],
    'mod_timetableevents_select_groups' => [
        'classname' => 'mod_timetableevents\\external\\get_groups',
        'methodname' => 'get_groups',
        'description' => 'Select groups in a different course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/timetableevents:addinstance'
    ]
];
