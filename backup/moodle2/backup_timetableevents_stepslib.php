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
 * Define all the backup steps that will be used by the backup_timetableevents_activity_task
 * @package mod_timetableevents
 * @copyright   2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author      Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete timetable events structure for backup, with file and id annotations
 */
class backup_timetableevents_activity_structure_step extends backup_activity_structure_step {
    /**
     * List of elements that can be backed up.
     * @return \backup_nested_element
     */
    protected function define_structure(): \backup_nested_element {

        // Define each element separated.
        $timetableevent = new backup_nested_element(
            'timetableevents',
            ['id'],
            [
                'courseoverride',
                'name',
                'intro',
                'introformat',
                'groupid',
                'startdate',
                'enddate',
            ]
        );

        // Define sources.
        $timetableevent->set_source_table('timetableevents', ['id' => backup::VAR_ACTIVITYID]);

        // Return the root element (timetableevents), wrapped into standard activity structure.
        return $this->prepare_activity_structure($timetableevent);
    }
}
