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
 * Defines all the restore steps that will be used by the restore_timetableevents_activity_task
 *
 * @package    mod_timetableevents
 * @subpackage backup-moodle2
 * @copyright  2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author     Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one timetableevents activity
 */
class restore_timetableevents_activity_structure_step extends restore_activity_structure_step {

    /**
     * List of elements that can be restored.
     * @return array
     */
    protected function define_structure() {

        $paths = [];
        $paths[] = new restore_path_element('timetableevents', '/activity/timetableevents');

        // Return the paths wrapped into standard activity structure.
        $return = $this->prepare_activity_structure($paths);
        return $return;
    }

    /**
     * Process timetablevent information.
     * @param array $data information
     */
    protected function process_timetableevents(array $data) {
        global $DB, $USER;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->courseoverride = null;

        // Insert the timetableevents record.
        $time = new DateTime("now", core_date::get_server_timezone_object());
        $data->usermodified = $USER->id;
        $data->timecreated = $time->getTimestamp();
        $data->timemodified = $time->getTimestamp();

        $newitemid = $DB->insert_record('timetableevents', $data);
        \mod_timetableevents\data_manager::set_course_defaults((array) $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Define data processed after execute for timetable event.
     */
    protected function after_execute() {

    }

}
