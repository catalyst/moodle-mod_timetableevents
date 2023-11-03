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
 * Class for data management.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents;

use cm_info;
use mod_timetableevents\output\group_overrides;
use mod_timetableevents\output\location;
use stdClass;

/**
 * Class for data management.
 */
class data_manager {
    /**
     * Override values.
     *
     * @param stdClass $timetableevent The timetableevent instance.     *
     * @return stdClass
     */
    public static function override_values(stdClass $timetableevent): stdClass {
        if (!isset($timetableevent->courseoverride)) {
            $timetableevent->courseoverride = null;
        }

        if (!isset($timetableevent->groupid) || $timetableevent->groupid == 0) {
            $timetableevent->groupid = null;
        }

        if (!isset($timetableevent->courseoverride) && !isset($timetableevent->coursedefaults)) {
            $timetableevent->courseoverride = $timetableevent->course;
        }

        if (isset($timetableevent->coursedefaults)) {
            $timetableevent->courseoverride = null;
            $timetableevent->groupid = null;
        }

        if (!isset($timetableevent->startdate) || $timetableevent->startdate == 0) {
            $timetableevent->startdate = 0;
            $timetableevent->enddate = 0;
        }

        return $timetableevent;
    }

    /**
     * Get course sections.
     *
     * @param int $courseid Course ID.
     * @return array $sections
     */
    public static function get_course_sections(int $courseid): array {
        global $DB;

        $sectioninfo = $DB->get_records('course_sections', ['course' => $courseid]);
        $sections = [];

        foreach ($sectioninfo as $record) {
            $section = new stdClass();
            $section->id = null;
            $section->name = null;
            $section->section = null;
            $section->sectionid = null;

            $section->id = $record->id;
            $section->name = $record->name;
            $section->section = $record->section;
            $section->sectionid = $record->id;

            $sections[$record->section] = $section;
        }

        return $sections;
    }

    /**
     * Combine data from different fields to be added to the same DB record.
     *
     * @param stdClass $data Course ID.
     * @return array
     */
    public static function create_section_objects(stdClass $data): array {

        global $DB;
        // Get all course sections.
        $sections = $DB->get_records_sql(
            "SELECT cs.id AS sectionid, cs.section, ts.id, ts.excluded, ts.readingweek
               FROM {course_sections} cs
               JOIN {course} c ON c.id = cs.course
          LEFT JOIN {timetableevents_section} ts ON ts.sectionid = cs.id
              WHERE cs.course = ?",
            [$data->course]
        );

        $sectionobjs = [];

        if ($sections) {
            foreach ($sections as $section) {
                // Create a new object in case we need it.
                $sectionobj = new stdClass();
                $sectionobj->sectionid = $section->sectionid;

                // If the section appears in either included or readinglist array.
                if (
                    in_array($section->sectionid, $data->excluded) ||
                    in_array($section->sectionid, $data->readingweek)
                ) {
                    // If we're adding or updating data.
                    // If the section is to be excluded.
                    if (in_array($section->sectionid, $data->excluded)) {
                        $sectionobj->excluded = 1;
                    } else {
                        $sectionobj->excluded = 0;
                    }

                    $sectionobjs[$section->sectionid] = $sectionobj;

                    // If the section is a reading week.
                    if (in_array($section->sectionid, $data->readingweek)) {
                        $sectionobj->readingweek = 1;
                    } else {
                        $sectionobj->readingweek = 0;
                    }

                    if (!array_key_exists($section->sectionid, $sectionobjs)) {
                        $sectionobjs[$section->sectionid] = $sectionobj;
                    }
                }

                // If we're removing data.
                if (
                    !in_array($section->sectionid, $data->excluded)
                    && $section->excluded == 1
                ) {
                    $sectionobj->excluded = 0;
                    if (!array_key_exists($section->sectionid, $sectionobjs)) {
                        $sectionobjs[$section->sectionid] = $sectionobj;
                    }
                }

                if (
                    !in_array($section->sectionid, $data->readingweek)
                    && $section->readingweek == 1
                ) {
                    $sectionobj->readingweek = 0;
                    if (!array_key_exists($section->sectionid, $sectionobjs)) {
                        $sectionobjs[$section->sectionid] = $sectionobj;
                    }
                }

                // If the teaching interval is not fortnightly, remove reading week section.
                if (
                    $data->teachinginverval != teaching_intervals::FORTNIGHTLY &&
                    $section->readingweek == 1
                ) {
                    $sectionobj->readingweek = 0;
                }
            }
        }

        return $sectionobjs;
    }

    /**
     * Create an academic year.
     * @param string $yearname
     * @return int
     */
    public static function create_academic_year(string $yearname): int {
            global $DB;
            $yearobj = new stdClass();
            $yearobj->name = $yearname;

            $yearid = $DB->insert_record('timetableevents_year', $yearobj);

            return $yearid;
    }

    /**
     * Create academic year.
     * @param stdClass $terms
     * @param int $yearid
     * @return void
     */
    public static function create_academic_terms(stdClass $terms, int $yearid): void {
        global $DB;

        foreach ($terms->startdate as $key => $value) {
            $termobj = new stdClass();
            $termobj->yearid = $yearid;

            $termstart = new \DateTime('now', \core_date::get_server_timezone_object());
            $termstart->setTimestamp($value);
            $termstart->setTime(00, 00, 00);

            $termend = new \DateTime('now', \core_date::get_server_timezone_object());
            $termend->setTimestamp($terms->enddate[$key]);
            $termend->setTime(23, 59, 59);

            $termobj->startdate = $termstart->getTimestamp();
            $termobj->enddate = $termend->getTimestamp();

            $DB->insert_record('timetableevents_term', $termobj);
        }
    }

    /**
     * Create or update an academic term.
     *
     * @param stdClass $terms
     * @return void
     */
    public static function update_academic_terms(stdClass $terms): void {
        global $DB;

        foreach ($terms->termid as $key => $term) {
            $termobj = new stdClass();
            $termobj->id = $term;
            $termobj->yearid = $terms->yearid;

            $termstart = new \DateTime('now', \core_date::get_server_timezone_object());
            $termstart->setTimestamp($terms->startdate[$key]);
            $termstart->setTime(00, 00, 00);

            $termend = new \DateTime('now', \core_date::get_server_timezone_object());
            $termend->setTimestamp($terms->enddate[$key]);
            $termend->setTime(23, 59, 59);

            $termobj->startdate = $termstart->getTimestamp();
            $termobj->enddate = $termend->getTimestamp();

            $termrecord = $DB->get_record('timetableevents_term', ['id' => $term]);

            if (!$termrecord) {
                $DB->insert_record('timetableevents_term', $termobj);
            } else {
                if ($termrecord->startdate != $termobj->startdate || $termrecord->enddate != $termobj->enddate) {
                    $DB->update_record('timetableevents_term', $termobj);
                }
            }
        }
    }

    /**
     * Get configured academic years.
     *
     * @return array
     */
    public static function get_acadyears(): array {
        global $DB;

        return $DB->get_records('timetableevents_year');
    }

    /**
     * Get academic years that are in use.
     *
     * @return array
     */
    public static function get_acadyears_in_use(): array {
        global $DB;
        $acadyears = $DB->get_records_sql(
            "SELECT DISTINCT(tt.yearid)
               FROM {timetableevents_course} tc
               JOIN {timetableevents_term} tt ON tc.startingtermid = tt.id"
        );
        return $acadyears;
    }

    /**
     * Get all terms or terms by year.
     *
     * @param string|null $id Year ID.
     * @return array
     */
    public static function get_terms(string $id = null): array {
        global $DB;

        $where = ' WHERE ty.id = ?';
        $order = ' ORDER BY ty.id, tt.id';
        $params = null;
        $sql = 'SELECT tt.id AS termid, yearid, name AS year, startdate, enddate
                  FROM {timetableevents_year} ty
                  JOIN {timetableevents_term} tt ON tt.yearid = ty.id';
        if ($id) {
            $sql .= $where;
            $sql .= $order;
            $params = [$id];
        } else {
            $sql .= $order;
        }
        $terms = $DB->get_records_sql($sql, $params);

        $termsbyyear = [];
        $termno = 1;
        $yearid = 1;
        $tz = \core_date::get_server_timezone_object();

        foreach ($terms as $term) {
            if ($term->yearid != $yearid) {
                $termno = 1;
            }

            $startdate = \DateTime::createFromFormat('U', $term->startdate, $tz);
            $enddate = \DateTime::createFromFormat('U', $term->enddate, $tz);

            $term->startdateformatted = date('d/m/Y', $startdate->getTimestamp());
            $term->enddateformatted = date('d/m/Y', $enddate->getTimestamp());
            $term->day = date('d', $startdate->getTimestamp());
            $term->month = date('m', $startdate->getTimestamp());
            $term->yearname = $term->year;
            $term->year = date('Y', $startdate->getTimestamp());
            $term->termno = $termno;
            $term->termname = get_string('coursesetting:groupoverride:term', 'timetableevents', ['termno' => $termno]);
            $termsbyyear[$term->yearid][$term->termid] = $term;
            $yearid = $term->yearid;
            $termno++;
        }

        return $termsbyyear;
    }

    /**
     * Get stored data for the course settings form.
     *
     * @param int $courseid Course ID.
     * @return stdClass
     */
    public static function get_course_form_data(int $courseid): stdClass {
        global $DB;

        $coursedata = $DB->get_record_sql(
            "SELECT tc.*, tt.yearid AS academicyear, tt.id AS term
               FROM {timetableevents_course} tc
               JOIN {timetableevents_term} tt ON tt.id = tc.startingtermid
              WHERE courseid = ?",
            [$courseid]
        );

        $sections = $DB->get_records_sql(
            "SELECT ts.id, ts.sectionid, ts.excluded, ts.readingweek
               FROM {timetableevents_section} ts
               JOIN {course_sections} cs ON ts.sectionid = cs.id
               JOIN {course} c ON c.id = cs.course
              WHERE cs.course = ?",
            [$courseid]
        );

        $excluded = [];
        $readingweek = [];

        foreach ($sections as $section) {
            if ($section->excluded == 1) {
                $excluded[$section->sectionid] = $section->sectionid;
            }

            if ($section->readingweek == 1) {
                $readingweek[$section->sectionid] = $section->sectionid;
            }
        }
        $coursedata = $coursedata ?: new stdClass();
        $coursedata->excluded = $excluded;
        $coursedata->readingweek = $readingweek;

        return $coursedata;
    }

    /**
     * Get course groups.
     *
     * @param int $courseid Course ID.
     * @return array
     */
    public static function get_groups(int $courseid): array {
        // Get groups via core method.
        $groups = groups_get_all_groups($courseid);
        // Get groups overrides.
        $groupoverrides = (new group_overrides($courseid))->get_group_overrides();

        // Remove groups that already have an override.
        foreach ($groups as $group) {
            if (array_key_exists($group->id, $groupoverrides)) {
                unset($groups[$group->id]);
            }
        }

        return $groups;
    }

    /**
     * Get groups for a different course, only if timetableevents events exist for the group.
     *
     * @param int $courseid Course ID.
     * @return array
     */
    public static function get_other_course_groups(int $courseid): array {
        global $DB;

        $sql = "SELECT DISTINCT(g.id), g.name
                  FROM {event} e
                  JOIN {groups} g ON e.courseid = g.courseid AND e.groupid = g.id
                 WHERE component = 'mod_timetableevents'
                   AND g.courseid = ?
                 ORDER BY g.name";

        $groups = $DB->get_records_sql($sql, [$courseid]);

        return $groups;
    }

    /**
     * Get the events.
     *
     * @param stdClass $instance The module instance.
     * @param array $daterange Date range of the section.
     * @param stdClass $groupdata User groups and event type.
     * @param int $grouppreference User's current display group preference.
     * @return array
     */
    public static function get_events(
        stdClass $instance,
        array $daterange,
        stdClass $groupdata,
        int $grouppreference
    ): array {
        global $DB, $PAGE;

        $groupdatacopy = clone($groupdata);
        $eventtype = $groupdatacopy->eventtype;
        unset($groupdatacopy->eventtype);
        $params = [];
        $params['courseid'] = $instance->course;
        $params['eventtype'] = 'group';

        if (!is_null($instance->courseoverride) && $instance->courseoverride != $instance->course) {
            // Only show events for the alternate selected course group.
            $params['courseid'] = $instance->courseoverride;
        }

        // If the instance has a group override, use the date range for that group.
        if (!is_null($instance->groupid)) {
            $groupid = $instance->groupid;
            $daterange = $daterange[$instance->groupid];
        } else {
            // Otherwise use the user preference.
            $groupid = $grouppreference;
            $daterange = $daterange[$grouppreference];
        }

        // Event type to display (course or group).
        if ($eventtype) {
            if (in_array('course', $eventtype)) {
                $params['eventtype'] = 'course';
                $currentgroupand = '';
            } else {
                $params['groupid'] = $groupid;
                $currentgroupand = " AND groupid = :groupid";
            }
        }

        $params['component'] = 'mod_timetableevents';
        $params['timestartgreater'] = $daterange['starttimestamp'];
        $params['timestartless'] = $daterange['endtimestamp'];

        $eventssql = "SELECT *
                        FROM {event}
                       WHERE courseid = :courseid
                         AND component = :component
                         $currentgroupand
                         AND eventtype = :eventtype
                         AND (timestart >= :timestartgreater
                         AND timestart <= :timestartless)";

        $events = $DB->get_records_sql($eventssql, $params);

        $tz = get_user_timezone();

        foreach ($events as $event) {
            $location = new location($event->location);
            $renderer = $PAGE->get_renderer('mod_timetableevents');
            $event->location = $renderer->render($location);
            // Add event duration to start time.
            $dateend = $event->timestart + $event->timeduration;
            // Generate event start and end times based on user's timezone.
            $event->timeend = userdate($dateend, get_string('strftimetime24', 'langconfig'), $tz);
            $event->timestart = userdate($event->timestart, get_string('strftimedatetimeshort', 'langconfig'), $tz);
        }

        return array_values($events);
    }

    /**
     * Get all group events.
     *
     * @param int $courseid The course id.
     * @param int $groupid The group id.
     * @return array
     */
    public static function get_all_group_events(int $courseid, int $groupid): array {
        global $DB;

        $params['courseid'] = $courseid;
        $params['component'] = 'mod_timetableevents';
        $params['groupid'] = $groupid;
        $params['eventtype'] = 'group';

        $eventssql = "SELECT *
                        FROM {event}
                       WHERE courseid = :courseid
                         AND component = :component
                         AND groupid = :groupid
                         AND eventtype = :eventtype";

        return $DB->get_records_sql($eventssql, $params);
    }

    /**
     * Get the user groups and event type.
     *
     * @param cm_info $cm Course module.
     * @param int $courseid Course ID.
     * @param \context_module $context Module context.
     * @return stdClass
     */
    public static function get_user_groups_and_event_type(cm_info $cm, int $courseid, \context_module $context): stdClass {
        global $USER;

        $groupmode = groups_get_activity_groupmode($cm, $courseid);

        $groupdata = new stdClass();
        $groupdata->groups = [];
        $groupdata->eventtype = ['group'];

        // If group mode is not enabled.
        if ($groupmode == NOGROUPS) {
            $groupdata->eventtype = ['course'];
        }

        $allgroups = groups_get_all_groups($courseid);
        // Remove any groups that a user isn't a member of or that don't have any events.
        if (!has_capability('mod/timetableevents:viewall', $context)) {
            $usergroups = groups_get_user_groups($courseid, $USER->id);
            foreach ($allgroups as $group) {
                $events = self::get_all_group_events($courseid, $group->id);
                if (!in_array($group->id, $usergroups[0]) || count($events) == 0) {
                    unset($allgroups[$group->id]);
                }
            }
        }

        $groups = [];
        foreach ($allgroups as $group) {
            $groups[$group->id] = $group->name;
            $groupdata->groups = $groups;
        }

        return $groupdata;
    }

    /**
     * Get the site config settings.
     * @return array
     */
    public static function get_site_config(): array {
        $footertext = get_config('mod_timetableevents', 'footertext');
        $config['footertext'] = $footertext;
        return $config;
    }

    /**
     * Get the course config settings.
     *
     * @param int $courseid Course id.
     * @return stdClass
     */
    public static function get_course_config(int $courseid): stdClass {
        global $DB;

        $courseconfig = $DB->get_record_sql(
            "SELECT tc.*
                    FROM {timetableevents_course} tc
                   WHERE tc.courseid = ?",
            [$courseid]
        );

        $groups = $DB->get_records_sql(
            "SELECT tg.*
                    FROM {timetableevents_course} tc
                    JOIN {groups} g ON g.courseid = tc.courseid
                    JOIN {timetableevents_group} tg on tg.groupid = g.id
                   WHERE tc.courseid = ?",
            [$courseid]
        );

        $sections = $DB->get_records_sql(
            "SELECT cs.id AS sectionid, cs.section, ts.id, ts.excluded, ts.readingweek
                   FROM {course_sections} cs
                   JOIN {course} c ON c.id = cs.course
                   JOIN {timetableevents_section} ts ON ts.sectionid = cs.id
                  WHERE cs.course = ?",
            [$courseid]
        );

        $courseconfig->groupoverrides = [];

        if ($groups) {
            foreach ($groups as $group) {
                $groupoverride = new stdClass();
                $groupoverride->groupid = $group->groupid;
                $groupoverride->startingtermid = $group->startingtermid;
                $groupoverride->teachingstartdate = $group->teachingstartdate;
                $courseconfig->groupoverrides[] = $groupoverride;
            }
        }

        if ($sections) {
            $courseconfig->sectionoverrides = $sections;
        }

        return $courseconfig;
    }

    /**
     * Get an array of the excluded sections.
     *
     * @param stdClass $courseconfig The course config settings.
     * @return array
     */
    public static function get_excluded_sections(stdClass $courseconfig): array {
        $excludedsections = [];
        if (isset($courseconfig->sectionoverrides)) {
            $overrides = (array_values($courseconfig->sectionoverrides));

            foreach ($overrides as $override) {
                if ($override->excluded == 1) {
                    $excludedsections[$override->section] = $override;
                }
            }
        }

        return $excludedsections;
    }

    /**
     * Get an array of the reading week sections.
     *
     * @param stdClass $courseconfig The course config settings.
     * @return array
     */
    public static function get_reading_weeks(stdClass $courseconfig): array {
        $readingweeks = [];
        if (isset($courseconfig->sectionoverrides)) {
            $overrides = (array_values($courseconfig->sectionoverrides));
            foreach ($overrides as $override) {
                if ($override->readingweek == 1) {
                    $readingweeks[$override->section] = $override;
                }
            }
        }

        return $readingweeks;
    }

    /**
     * Calculate the section date ranges.
     *
     * @param stdClass $cm The course module.
     * @param stdClass $instance The module instance
     * @param stdClass $courseconfig The course config settings
     * @param int|null $group The group ID to calculate the date range for.
     * @return array
     */
    public static function calculate_date_range(
        stdClass $cm,
        stdClass $instance,
        stdClass $courseconfig,
        int $group = null
    ): array {
        global $DB;
        $daterange = [];

        // Get the teaching start date.
        $teachingstartdate = new \DateTime('now', \core_date::get_server_timezone_object());
        $teachingstartdate->setTimestamp($courseconfig->teachingstartdate);

        // If there's a group or instance override, use that instead.
        if ($group != 0) {
            $teachingstartdateoverride = self::get_teaching_startdate_overrides($courseconfig, $instance, $group);
            $teachingstartdate->setTimestamp($teachingstartdateoverride->teachingstartdate);
        }

        $teachingstartdate->setTime(00, 00);
        $currentsection = $DB->get_field('course_sections', 'section', ['id' => $cm->section]);
        $firstteachingsection = $courseconfig->firstsection;

        // Only work out the date range for the first teaching section and beyond.
        if ($currentsection >= $firstteachingsection) {
            $sections = self::get_course_sections($courseconfig->courseid);
            $readingweeks = self::get_reading_weeks($courseconfig);
            $excluded = self::get_excluded_sections($courseconfig);
            $startingtermid = $courseconfig->startingtermid;
            $year = $DB->get_field('timetableevents_term', 'yearid', ['id' => $startingtermid]);
            $terms = self::get_terms($year);
            $terms = $terms[$year];
            $previouskey = null;

            switch ($courseconfig->teachinginverval) {
                case teaching_intervals::WEEKLY:
                    $teachingrange = 1;
                    $unit = 'W';
                    break;

                case teaching_intervals::FORTNIGHTLY:
                    $teachingrange = 2;
                    $unit = 'W';
                    break;

                case teaching_intervals::DAILY:
                    $teachingrange = 1;
                    $unit = 'D';
                    break;

                default:
                    '';
                    break;
            }

            // If the instance has start and end date overrides, use those.
            if ($instance->startdate != 0 && $instance->enddate != 0) {
                $daterangestart = new \DateTime('now', \core_date::get_server_timezone_object());
                $daterangestart->setTimestamp($instance->startdate);

                $daterangeend = new \DateTime('now', \core_date::get_server_timezone_object());
                $daterangeend->setTimestamp($instance->enddate);
                $daterangeend->setTime(23, 59, 59);
            } else {
                // Get all sections between the first teaching section and current section.
                $teachingsections = [];

                foreach ($sections as $section) {
                    if ($section->section >= $firstteachingsection && $section->section <= $currentsection) {
                        $teachingsections[$section->section] = $section;
                    }
                }

                foreach ($teachingsections as $teachingsectionkey => $teachingsection) {
                    $teachinginterval = 'P' . $teachingrange . $unit;
                    $daterangestart = new \DateTime('now', \core_date::get_server_timezone_object());
                    $daterangeend = new \DateTime('now', \core_date::get_server_timezone_object());

                    // If this section is >= the first teaching section.
                    if ($teachingsection->section >= $firstteachingsection) {
                        // If an end date exists for the previous section,
                        // increase by one second and set that as the current section start date.
                        if (array_key_exists($previouskey, $teachingsections)) {
                            $daterangestart->setTimestamp($teachingsections[$previouskey]->sectionenddate->getTimestamp());
                            $interval = 'PT1S';
                            $daterangestart->add(new \DateInterval($interval));
                        } else {
                            $daterangestart->setTimestamp($teachingstartdate->getTimestamp());
                        }

                        $daterangeend->setTimestamp($daterangestart->getTimestamp() - 1);

                        // If the section is excluded, add 0 to the end date.
                        if (array_key_exists($teachingsection->section, $excluded)) {
                            $teachingsections[$teachingsectionkey]->excluded = 1;
                            $teachingsections[$teachingsectionkey]->readingweek = 0;
                            $teachinginterval = 'P0' . $unit;
                            $daterangestart->add(new \DateInterval($teachinginterval));
                            $daterangeend->add(new \DateInterval($teachinginterval));
                        }

                        if (!array_key_exists($teachingsection->section, $excluded)) {
                            // If interval is Fortnightly and the section is a reading week, add one week to the end date.
                            if (
                                array_key_exists($teachingsection->section, $readingweeks)
                                && $courseconfig->teachinginverval == teaching_intervals::FORTNIGHTLY
                            ) {
                                $teachingsections[$teachingsectionkey]->excluded = 0;
                                $teachingsections[$teachingsectionkey]->readingweek = 1;
                                $teachinginterval = 'P1W';
                            }

                            // Otherwise, add the default teaching interval to the end date.
                            if (
                                !array_key_exists($teachingsection->section, $excluded)
                                && !array_key_exists($teachingsection->section, $readingweeks)
                            ) {
                                $teachingsections[$teachingsectionkey]->excluded = 0;
                                $teachingsections[$teachingsectionkey]->readingweek = 0;
                            }
                        }
                        // New end date.
                        $daterangeend->add(new \DateInterval($teachinginterval));

                        // Now make term adjustments!
                        // Add one second to the start date of the current section and see if it's greater
                        // than the end of the last term end date.
                        $newdaterangestart = new \DateTime('now', \core_date::get_server_timezone_object());
                        $newdaterangestart->setTimestamp($daterangestart->getTimestamp());
                        $interval = 'PT1S';
                        $newdaterangestart->add(new \DateInterval($interval));

                        // Work out which term the section is in and calculate any term differences that need to be added.
                        foreach ($terms as $termkey => $term) {
                            $previoustermkey = $termkey - 1;

                            $termstart = new \DateTime('now', \core_date::get_server_timezone_object());
                            $termstart->setTimestamp($term->startdate);
                            $termstart->setTime(00, 00);

                            if (array_key_exists($previoustermkey, $terms)) {
                                $previoustermend = new \DateTime('now', \core_date::get_server_timezone_object());
                                $previoustermend->setTimestamp($terms[$previoustermkey]->enddate);
                                $previoustermend->setTime(23, 59, 59);
                            }

                            if (array_key_exists($previoustermkey, $terms)) {
                                if (
                                    $newdaterangestart->getTimestamp() > $previoustermend->getTimestamp()
                                    && $newdaterangestart->getTimestamp() < $termstart->getTimestamp()
                                ) {
                                    // Add difference between term dates.
                                    if ($teachingsections[$teachingsectionkey]->excluded == 0) {
                                        $diff = date_diff($previoustermend, $termstart);
                                        $daterangestart->add(new \DateInterval('P' . $diff->days . 'D'));
                                        $daterangeend->add(new \DateInterval('P' . $diff->days . 'D'));
                                        break;
                                    }
                                }
                            }
                        }

                        // Make any adjustments required to the end date if we hit the end of a term.
                        foreach ($terms as $termkey => $term) {
                            $nexttermkey = $termkey + 1;

                            $termend = new \DateTime('now', \core_date::get_server_timezone_object());
                            $termend->setTimestamp($term->enddate);
                            $termend->setTime(23, 59, 59);

                            if (array_key_exists($nexttermkey, $terms)) {
                                $nexttermstart = new \DateTime('now', \core_date::get_server_timezone_object());
                                $nexttermstart->setTimestamp($terms[$nexttermkey]->startdate);
                                $nexttermstart->setTime(00, 00);

                                // If the new calculated date is greater than this term's end date and is less than
                                // the start of the next term's start date.
                                if (
                                    $daterangeend->getTimestamp() > $termend->getTimestamp()
                                    && $daterangeend->getTimestamp() < $nexttermstart->getTimestamp()
                                ) {
                                    // Set the end date of the section to the end date of the term.
                                    $daterangeend->setTimestamp($term->enddate);
                                    $daterangeend->setTime(23, 59, 59);
                                }
                            }
                        }

                        $teachingsections[$teachingsectionkey]->sectionstartdate = $daterangestart;
                        $teachingsections[$teachingsectionkey]->sectionenddate = $daterangeend;

                        $previouskey = $teachingsectionkey;
                    }
                }
            }

            $daterange['starttimestamp'] = $daterangestart->getTimestamp();
            $daterange['endtimestamp'] = $daterangeend->getTimestamp();

            $daterange['teach'] = date('Y/m/d, H:i', $teachingstartdate->getTimestamp());
            $daterange['start'] = date('Y/m/d, H:i', $daterangestart->getTimestamp());
            $daterange['end'] = date('Y/m/d, H:i', $daterangeend->getTimestamp());
            $daterange['course'] = $courseconfig->courseid;
            $daterange['cmid'] = $cm->id;
            $daterange['group'] = $group;
        }

        return $daterange;
    }

    /**
     * Create a group availability restriction for an instance with a group override.
     *
     * @param stdClass $courseconfig The course config settings.
     * @param stdClass $instance The instance data.
     * @param int $group The instance group override ID.
     * @return stdClass
     */
    public static function get_teaching_startdate_overrides(
        stdClass $courseconfig,
        stdClass $instance,
        int $group
    ): stdClass {

        $data = new stdClass();
        $data->teachingstartdate = $courseconfig->teachingstartdate;
        $data->group = 0;

        // Check if there are any instance overrides.
        // If the dates have been overridden, use those.
        if ($instance->startdate != 0) {
            $data->teachingstartdate = $instance->startdate;
        }

        // If a group is assigned at instance level and there's an override for that group
        // use that group's teaching start date.
        if (count($courseconfig->groupoverrides) > 0) {
            foreach ($courseconfig->groupoverrides as $groupoverride) {
                if ($group == $groupoverride->groupid) {
                    $data->teachingstartdate = $groupoverride->teachingstartdate;
                }
            }
        }

        return $data;
    }

    /**
     * If required, set the course settings based on site defaults.
     *
     * @param array $defaultvalues The form data.
     * @return void
     */
    public static function set_course_defaults(array $defaultvalues): void {
        global $DB;
        // Add course settings based on site config if they're not already set.
        $coursesettings = $DB->get_record('timetableevents_course', ['courseid' => $defaultvalues['course']]);

        if (!$coursesettings) {
            // Check if the plugin has been configured.
            $pluginsettings = get_config('mod_timetableevents');
            if ($pluginsettings->currentacadyear != 0) {
                // Set default course data.
                $settingsobj = new stdClass();
                $settingsobj->courseid = $defaultvalues['course'];
                $terms = self::get_terms($pluginsettings->currentacadyear);
                if ($terms) {
                    $terms = array_values($terms);
                    $terms = array_values($terms[0]);
                    $settingsobj->startingtermid = $terms[0]->termid;
                }

                $settingsobj->teachingstartdate = $pluginsettings->teachingstartdate;

                $coursesections = self::get_course_sections($defaultvalues['course']);
                $settingsobj->firstsection = 0;
                foreach ($coursesections as $section) {
                    if ($section->section == $pluginsettings->firstteachingsection) {
                        $settingsobj->firstsection = $section->section;
                    }
                }
                $settingsobj->teachinginverval = $pluginsettings->teachinginterval;
                $DB->insert_record('timetableevents_course', $settingsobj);
            } else {
                // If the site settings haven't been configured, throw an exception and return user to course page.
                $url = new \moodle_url("/course/view.php", ['id' => $defaultvalues['course']]);
                throw new \moodle_exception('notconfigured', 'mod_timetableevents', $url);
            }
        }
    }

    /**
     * Create a group availability restriction for an instance with a group override.
     *
     * @param stdClass $data The form data.
     * @return void
     */
    public static function update_availability(stdClass $data): void {
        // Get the availability data being sent from the form.
        $formavailability = json_decode($data->availabilityconditionsjson);
        $newgrouprestriction = null;

        // Check if the correct group restriction is already being set.
        if ($formavailability->c) {
            foreach ($formavailability->c as $c) {
                if ($c->type == 'group' && $c->id == $data->groupid) {
                    $newgrouprestriction = $c;
                }
            }
        }

        // If not, add it to the form data.
        if (!$newgrouprestriction) {
            // Create a new group restriction.
            $restriction = \core_availability\tree::get_root_json(
                [\availability_group\condition::get_json($data->groupid)],
                \core_availability\tree::OP_AND,
                false
            );

            $formavailability->c[] = $restriction->c[0];
            $formavailability->showc[] = false;
            $data->availabilityconditionsjson = json_encode($formavailability);
        }
    }

    /**
     * Check the user's current group preference and update if group membership has changed.
     *
     * @param int|null $grouppreference The user's display group preference.
     * @param array $groups The user's current groups.
     * @param int $courseid The course ID.
     * @return int
     */
    public static function check_and_update_group_preference(int $grouppreference = null, array $groups, int $courseid): int {

        // If a preference hasn't been set, set the first group as the preference.
        if (!$grouppreference && count($groups) > 0) {
            $grouppreference = key($groups);
            set_user_preference('mod_timetableevents_' . $courseid, $grouppreference);
        }
        // If the group preference has been set but the user isn't in that group any more,
        // set the group to the first group they are a member of.
        if ($grouppreference && count($groups) > 0) {
            if (!array_key_exists($grouppreference, $groups)) {
                $grouppreference = key($groups);
            }
            set_user_preference('mod_timetableevents_' . $courseid, $grouppreference);
        }

        // If the user isn't in any groups, set the preference to 0.
        if (!$grouppreference || count($groups) == 0) {
            $grouppreference = 0;
            set_user_preference('mod_timetableevents_' . $courseid, 0);
        }

        return $grouppreference;
    }
}
