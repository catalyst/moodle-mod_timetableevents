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
 * Import timetable events to the calendar
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\external;

use mod_timetableevents\data_manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External function for getting properties of entity generators.
 */
class get_groups extends \external_api {

    /**
     * Define parameters for external function.
     *
     * @return \external_function_parameters
     */
    public static function get_groups_parameters() {
        return new \external_function_parameters(
            array(
                'courseid' => new \external_value(PARAM_INT),
            )
        );
    }
    /**
     * Define return values.
     *
     * @return \external_multiple_structure
     */
    public static function get_groups_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                array(
                    'id' => new \external_value(PARAM_INT, 'group record id'),
                    'name' => new \external_value(PARAM_TEXT, 'group name'),
                )
            )
        );
    }

    /**
     * Get groups for specified course.
     *
     * @param int $courseid The course to get groups for.
     * @return array
     */
    public static function get_groups(int $courseid): array {
        global $CFG;
        require_once("$CFG->dirroot/group/lib.php");
        $params = self::validate_parameters(self::get_groups_parameters(), ['courseid' => $courseid]);

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('mod/timetableevents:addinstance', $context);

        $groups = data_manager::get_other_course_groups($params['courseid']);

        return $groups;
    }
}
