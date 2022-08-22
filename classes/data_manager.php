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

use mod_timetableevents\output\group_overrides;
use stdClass;

/**
 * Class for data management.
 */
class data_manager {

    /**
     * Override values.
     *
     * @param stdClass $timetableevent The navigation node to extend
     * @return stdClass
     */
    public static function override_values(stdClass $timetableevent) : stdClass {
        if (!isset($timetableevent->courseoverride)) {
            $timetableevent->courseoverride = null;
        }

        if (!isset($timetableevent->groupid)) {
            $timetableevent->groupid = null;
        }

        if (!isset($timetableevent->courseoverride) && !isset($timetableevent->coursedefaults)) {
            $timetableevent->courseoverride = $timetableevent->course;
        }

        if (isset($timetableevent->coursedefaults)) {
            $timetableevent->courseoverride = null;
            $timetableevent->groupid = null;
        }

        return $timetableevent;
    }

    /**
     * Get course sections
     *
     * @param int $courseid Course ID
     * @return array $sections
     */
    public static function get_course_sections($courseid): array {

        global $DB;

        $sectionrecords = $DB->get_records('course_sections', ['course' => $courseid]);
        $sections = [];

        foreach ($sectionrecords as $record) {
            $record->name = get_section_name($courseid, $record->section);
            $sections[$record->id] = $record;
        }

        return $sections;
    }

    /**
     * Combine data from different fields to be added to the same DB record.
     *
     * @param stdClass $data Course ID
     * @return array
     */
    public static function create_section_objects(stdClass $data) : array {

        global $DB;
        // Get all course sections.
        $sections = $DB->get_records_sql(
            "SELECT cs.id AS sectionid, ts.id, ts.excluded, ts.readingweek
                   FROM {course_sections} cs
                   JOIN {course} c ON c.id = cs.course
              LEFT JOIN {timetableevents_section} ts ON ts.sectionid = cs.id
                  WHERE cs.course = ?", [$data->course]
        );

        $sectionobjs = [];

        if ($sections) {
            foreach ($sections as $section) {
                // Create a new object in case we need it.
                $sectionobj = new stdClass();
                $sectionobj->sectionid = $section->sectionid;

                // If the section appears in either included or readinglist array.
                if (in_array($section->sectionid, $data->excluded) ||
                    in_array($section->sectionid, $data->readingweek)) {

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
                if (!in_array($section->sectionid, $data->excluded)
                    && $section->excluded == 1) {
                    $sectionobj->excluded = 0;
                    if (!array_key_exists($section->sectionid, $sectionobjs)) {
                        $sectionobjs[$section->sectionid] = $sectionobj;
                    }
                }

                if (!in_array($section->sectionid, $data->readingweek)
                    && $section->readingweek == 1) {
                    $sectionobj->readingweek = 0;
                    if (!array_key_exists($section->sectionid, $sectionobjs)) {
                        $sectionobjs[$section->sectionid] = $sectionobj;
                    }
                }

                // If the teaching interval is not fortnightly, remove reading week section.
                if ($data->teachinginverval != teaching_intervals::FORTNIGHTLY &&
                    $section->readingweek == 1) {
                    $sectionobj->readingweek = 0;
                }
            }
        }

        return $sectionobjs;
    }

    /**
     * Get configured academic years.
     *
     * @return array
     */
    public static function get_acadyears() : array {
        global $DB;
        $acadyears = $DB->get_records('timetableevents_year');
        return $acadyears;
    }

    /**
     * Get academic years that are in use.
     *
     * @return array
     */
    public static function get_acadyears_in_use() : array {
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
     * @param string $id Year ID
     * @return array
     */
    public static function get_terms(string $id = null) : array {
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
     * @param int $courseid Course ID
     * @return stdClass
     */
    public static function get_course_form_data(int $courseid) : stdClass {
        global $DB;

        $coursedata = $DB->get_record_sql(
            "SELECT tc.*, tt.yearid AS academicyear, tt.id AS term
               FROM {timetableevents_course} tc
               JOIN {timetableevents_term} tt ON tt.id = tc.startingtermid
              WHERE courseid = ?", [$courseid]
        );

        $sections = $DB->get_records_sql(
            "SELECT ts.id, ts.sectionid, ts.excluded, ts.readingweek
               FROM {timetableevents_section} ts
               JOIN {course_sections} cs ON ts.sectionid = cs.id
               JOIN {course} c ON c.id = cs.course
              WHERE cs.course = ?", [$courseid]
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
        $coursedata = $coursedata ?: new stdClass;
        $coursedata->excluded = $excluded;
        $coursedata->readingweek = $readingweek;

        return $coursedata;
    }

    /**
     * Get course groups.
     *
     * @param int $courseid Course ID
     * @return array
     */
    public static function get_groups($courseid) : array {
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
}
