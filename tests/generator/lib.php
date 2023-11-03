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

use mod_timetableevents\data_manager;

/**
 * mod_timetableevents data generator class
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_timetableevents_generator extends testing_module_generator {
    /**
     * Create a new timetable events module instance.
     *
     * @param array|stdClass $record data for module being generated. Requires 'course' key
     *     (an id or the full object). Also can have any fields from add module form.
     * @param null|array $options general options for course module. Since 2.6 it is
     *     possible to omit this argument by merging options into $record
     * @return stdClass record from module-defined table with additional field
     *     cmid (corresponding id in course_modules table)
     */
    public function create_instance($record = null, array $options = null) {
        global $DB;
        $record = (array)$record;
        if (!$DB->record_exists('timetableevents_course', ['courseid' => $record['course']])) {
            data_manager::set_course_defaults(['course' => $record['course']]);
        }
        $record['showdescription'] = 1;
        $record['update'] = 0;
        if (!isset($record['coursedefaults'])) {
            $record['coursedefaults'] = 1;
        }
        return parent::create_instance($record, $options);
    }

    public function create_academic_year(array $data): int {
        return data_manager::create_academic_year($data['name']);
    }

    public function create_academic_term(array $data): void {
        global $DB;
        $yearid = $DB->get_field('timetableevents_year', 'id', ['name' => $data['name']]);
        $startdate = (int)$data['startdate'] == $data['startdate'] ? $data['startdate'] : strtotime($data['startdate']);
        $enddate = (int)$data['enddate'] == $data['enddate'] ? $data['enddate'] : strtotime($data['enddate']);
        $terms = (object)[
            'startdate' => [$startdate],
            'enddate' => [$enddate],
        ];
        data_manager::create_academic_terms($terms, $yearid);
    }

    public function create_event(array $data): void {
        $data['component'] = 'mod_timetableevents';
        $this->datagenerator->create_event($data);
    }
}
