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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * External function for getting properties of entity generators.
 */
class import_events extends \external_api {

    /**
     * @var string DATE_FORMAT
     *
     * The format to use for passing import timestart and timeend values.
     * We specify ISO 8601 format, but in practice this is a bit kinder and will allow any delimiter (such as a space)
     * between date and time sections.
     */
    const DATE_FORMAT = 'Y-m-d?H:i:s';

    const EVENT_COMPONENT = 'mod_timetableevents';

    const EVENT_TYPE = 'timetable';

    /**
     * Define parameters for external function.
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters(
            [
                'events' => new \external_multiple_structure(
                    new \external_single_structure([
                        'idnumber' => new \external_value(PARAM_ALPHANUMEXT,
                                'Unique ID of this event. Existing events with this ID will be updated.'),
                        'groupidnumber' => new \external_value(PARAM_ALPHANUMEXT,
                                'ID number of the group this event should display to.', VALUE_OPTIONAL),
                        'name' => new \external_value(PARAM_TEXT,
                                'Event name'),
                        'courseshortname' => new \external_value(PARAM_TEXT,
                                'Shortname of the course where this event should show.'),
                        'timestart' => new \external_value(PARAM_TEXT,
                               'Start date and time of the event, ISO 8601 format (YYYY-MM-DDThh:mm:ss)'),
                        'timeend' => new \external_value(PARAM_TEXT,
                                'End date and time of the event, ISO 8601 format (YYYY-MM-DDThh:mm:ss)'),
                        'location' => new \external_value(PARAM_TEXT,
                                'The location of the event'),
                    ], 'A single event.'),
                    'Events to be created or updated.'
                )
            ]
        );
    }

    /**
     * Import provided events.
     *
     * For each event passed, if the idnumber matches the uuid of an existing event, that event will be updated.
     * Otherwise, a new event will be created.
     *
     * @return array
     */
    public static function execute(array $events): array {
        global $DB;
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('mod/timetableevents:import', $context);

        $created = 0;
        $updated = 0;
        $warnings = [];
        $validgroups = [];
        $validcourses = [];

        foreach ($events as $key => $event) {
            // Check all provided references are valid.
            $courseid = null;
            $groupid = null;

            $courseid = array_search($event['courseshortname'], $validcourses);
            if ($courseid === false) {
                $courseid = $DB->get_field('course', 'id', ['shortname' => $event['courseshortname']]);
                if ($courseid) {
                    $validcourses[$courseid] = $event['courseshortname'];
                } else {
                    $warnings[] = self::create_warning($key, 'invalidcourseshortname',
                            $event['courseshortname']);
                    continue;
                }
            }

            if (empty($event['groupidnumber'])) {
                $groupid = 0;
            } else {
                $groupid = array_search($event['groupidnumber'], $validgroups);
                if ($groupid === false) {
                    $groupid = $DB->get_field('groups', 'id', ['idnumber' => $event['groupidnumber']]);
                    if ($groupid) {
                        $validgroups[$groupid] = $event['groupidnumber'];
                    } else {
                        $warnings[] = self::create_warning($key, 'invalidgroupidnumber', $event['groupidnumber']);
                        continue;
                    }
                }
            }

            try {
                $timestart = self::parse_time($event['timestart']);
                $timeend = self::parse_time($event['timeend']);
            } catch (\moodle_exception $e) {
                $warnings[] = self::create_warning($key, $e->errorcode, $e->debuginfo, $e->a);
                continue;
            }

            if ($timeend < $timestart) {
                $times = (object)['timestart' => $event['timestart'], 'timeend' => $event['timeend']];
                $warnings[] = self::create_warning($key, 'invalidtimeend', json_encode($times));
                continue;
            }

            $calendardata = (object)[
                'timestart' => $timestart->getTimestamp(),
                'timeduration' => $timeend->getTimestamp() - $timestart->getTimestamp(),
                'name' => $event['name'],
                'courseid' => $courseid,
                'groupid' => $groupid,
                'location' => $event['location']
            ];

             $params = [
                 'uuid' => $event['idnumber'],
                 'component' => self::EVENT_COMPONENT,
                 'eventtype' => self::EVENT_TYPE
             ];
             $eventid = $DB->get_field('event', 'id', $params);

             if ($eventid) {
                 $calendarevent = \calendar_event::load($eventid);
                 $calendarevent->update($calendardata, false);
                 $updated++;
             } else {
                 $calendardata->uuid = $event['idnumber'];
                 $calendardata->component = self::EVENT_COMPONENT;
                 $calendardata->eventtype = self::EVENT_TYPE;
                 \calendar_event::create($calendardata, false);
                 $created++;
             }
        }

        return ['created' => $created, 'updated' => $updated, 'warnings' => $warnings];
    }

    /**
     * Define return values.
     *
     * @return \external_single_structure
     */
    public static function execute_returns(): \external_single_structure {
        return new \external_single_structure([
            'created' => new \external_value(PARAM_INT, 'A count of newly created events.'),
            'updated' => new \external_value(PARAM_INT, 'A count of updated events.'),
            'warnings' => new \external_warnings('Invalid value', 'idnumber of invalid record'),
        ]);
    }

    /**
     * Parse the timestart or timeend string to a DateTime object.
     *
     * @return array Any errors that were produced
     */
    private static function parse_time($time) : \DateTime {
        $tz = \core_date::get_server_timezone_object();
        $datetime = \DateTime::createFromFormat(self::DATE_FORMAT, $time, $tz);
        if ($datetime === false) {
            $dterrors = \DateTime::getLastErrors();
            $messages = implode('; ', $dterrors['warnings']);
            $messages .= implode('; ', $dterrors['errors']);
            throw new \moodle_exception('invalidtime', 'mod_timetableevents', '', $messages, $time);
        }

        return $datetime;
    }

    /**
     * Generate an array of external_warning fields.
     *
     * @param string $key The array key of the event that caused the warning.
     * @param string $code The string identifier for this warning.
     * @param mixed $value The value that caused the warning (optional).
     * @param mixed $a Additional string paramters for the warning message (optional).
     * @return array
     * @throws \coding_exception
     */
    private static function create_warning(string $key, string $code, $value = null, $a = null) {
        return [
            'item' => $value,
            'itemid' => $key,
            'warningcode' => $code,
            'message' => get_string($code, 'mod_timetableevents', $a)
        ];
    }
}
