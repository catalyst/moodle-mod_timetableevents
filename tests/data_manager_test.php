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
 * Class for data_manager tests.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents;

use stdClass;
/**
 * Unit tests for data_manager class
 * @group timetableevents
 */
class data_manager_test extends \advanced_testcase {

    /**
     * Set up the test data.
     */
    public function setUp(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        // This timezone does not use DST (which would break the tests).
        $this->setTimezone(\core_date::get_server_timezone());

        $this->datamanager = new data_manager();
        $this->teachingintervals = new teaching_intervals();

        // Set up site level academic year.
        $yearid = data_manager::create_academic_year('2021-22');

        // Create terms for the academic year.
        // Term 1 12/09/2022 00:00 - 16/10/2022 23:59:59.
        // Term 2 31/10/2022 00:00 - 18/12/2022 23:59:59.
        $data = file_get_contents($CFG->dirroot . '/mod/timetableevents/tests/fixtures/acadyear_terms.json');
        $data = json_decode($data);
        $data->startdate = (array) $data->startdate;
        $data->enddate = (array) $data->enddate;
        data_manager::create_academic_terms($data, $yearid);

        $intervals = [
            $this->teachingintervals::WEEKLY => 'weekly',
            $this->teachingintervals::FORTNIGHTLY => 'fortnightly',
            $this->teachingintervals::DAILY => 'daily',
        ];

        // Create test courses.
        $this->create_courses($yearid, $intervals);

        // Get course config.
        $this->get_course_settings($intervals);

        // Create groups.
        $this->create_groups($intervals);

        // Create modules.
        $this->create_modules($intervals);

        // Create events.
        $this->create_events($intervals);

    }

    /**
     * Data provider for test_daterange_and_events_for_fortnightly_sections.
     * First teaching session cm 1 section 5
     * Excluded cm 2 section 6
     * Session 2 cm 3 section 7 Term 1 end 16/10/22
     * Excluded cm 4 section 8
     * Session 3 cm 5 section 9 Term 2 start 31/10/22
     * Reading week (1 week) cm 6 section 10
     */
    public function provider_fortnightly_sections(): array {
        return array(
            array(1, [
                'testgroupfortnightly1rangestart' => '2022/09/12, 00:00',
                'testgroupfortnightly1rangeend' => '2022/09/25, 23:59',
                'testgroupfortnightly2rangestart' => '2022/09/13, 00:00',
                'testgroupfortnightly2rangeend' => '2022/09/26, 23:59',
                'testgroupfortnightly1eventstart' => '12/09/22, 17:00',
                'testgroupfortnightly1eventend' => '18:00',
                'testgroupfortnightly1count' => '5',
                'testgroupfortnightly2eventstart' => '13/09/22, 17:00',
                'testgroupfortnightly2eventend' => '18:00',
                'testgroupfortnightly2count' => '5']),
            array(3, [
                'testgroupfortnightly1rangestart' => '2022/09/26, 00:00',
                'testgroupfortnightly1rangeend' => '2022/10/09, 23:59',
                'testgroupfortnightly2rangestart' => '2022/09/27, 00:00',
                'testgroupfortnightly2rangeend' => '2022/10/10, 23:59',
                'testgroupfortnightly1eventstart' => '28/09/22, 17:00',
                'testgroupfortnightly1eventend' => '18:00',
                'testgroupfortnightly1count' => '2',
                'testgroupfortnightly2eventstart' => '1/10/22, 18:00',
                'testgroupfortnightly2eventend' => '19:00',
                'testgroupfortnightly2count' => '2']),
            array(5, [
                'testgroupfortnightly1rangestart' => '2022/10/10, 00:00',
                'testgroupfortnightly1rangeend' => '2022/10/16, 23:59',
                'testgroupfortnightly2rangestart' => '2022/10/11, 00:00',
                'testgroupfortnightly2rangeend' => '2022/10/16, 23:59',
                'testgroupfortnightly1eventstart' => '12/10/22, 17:00',
                'testgroupfortnightly1eventend' => '18:00',
                'testgroupfortnightly1count' => '1',
                'testgroupfortnightly2eventstart' => '15/10/22, 17:00',
                'testgroupfortnightly2eventend' => '18:00',
                'testgroupfortnightly2count' => '1']),
            array(6, [
                'testgroupfortnightly1rangestart' => '2022/10/31, 00:00',
                'testgroupfortnightly1rangeend' => '2022/11/06, 23:59',
                'testgroupfortnightly2rangestart' => '2022/10/31, 00:00',
                'testgroupfortnightly2rangeend' => '2022/11/06, 23:59',
                'testgroupfortnightly1eventstart' => '31/10/22, 18:00',
                'testgroupfortnightly1eventend' => '19:00',
                'testgroupfortnightly1count' => '1',
                'testgroupfortnightly2eventstart' => '3/11/22, 18:00',
                'testgroupfortnightly2eventend' => '19:00',
                'testgroupfortnightly2count' => '1']),
        );
    }

    /**
     * Get events for fortnightly teaching interval (has excluded sections, no reading weeks).
     * @param int $cmid course module id
     * @param array $expectedresult
     * @dataProvider provider_fortnightly_sections
     */
    public function test_daterange_and_events_for_fortnightly_sections(int $cmid, array $expectedresult) {
        global $DB;
        $cm = 'testcoursefortnightlycm'. $cmid;

        $context = \context_module::instance($this->{$cm}->id);
        $cminfo = get_course_and_cm_from_cmid($this->{$cm}->id);

        $groupdata = data_manager::get_user_groups_and_event_type($cminfo[1], $this->testcoursefortnightly->id, $context);
        $groupdata->eventtype = ['group'];
        $instance = $DB->get_record('timetableevents', ['id' => $this->{$cm}->instance]);

        // Get cached date ranges.
        $cminfo = get_fast_modinfo($this->testcoursefortnightly->id);
        $daterange = $cminfo->get_cm($this->{$cm}->id)->customdata;
        $groups = [];
        $groups[1] = $this->testgroupfortnightly1;
        $groups[2] = $this->testgroupfortnightly2;

        // Get events and check the correct ones have been returned.
        foreach ($groups as $key => $group) {
            $this->assertEquals($expectedresult['testgroupfortnightly' . $key . 'rangestart'], $daterange[$group->id]['start']);
            $this->assertEquals($expectedresult['testgroupfortnightly' . $key . 'rangeend'], $daterange[$group->id]['end']);

            $events = $this->datamanager::get_events($instance, $daterange, $groupdata, $group->id);
            $this->assertEquals($expectedresult['testgroupfortnightly' . $key . 'eventstart'], $events[0]->timestart);
            $this->assertEquals($expectedresult['testgroupfortnightly' . $key . 'eventend'], $events[0]->timeend);
            $this->assertCount($expectedresult['testgroupfortnightly' . $key . 'count'], $events);
        }
    }

    /**
     * Data provider for test_daterange_and_events_for_weekly_sections.
     * Excluded - cm 2 (section 6), cm 4 (section 8).
     *
     * First teaching session cm 1 section 5
     * Excluded cm 2 section 6
     * Session 2 cm 3 section 11 Term 1 end 16/10/22
     * Session 3 cm 5 section 12 Term 2 start 31/10/22
     * Excluded cm 4 section 13
     */
    public function provider_weekly_sections(): array {
        return array(
            array(1, [
                'testgroupweekly1rangestart' => '2022/09/12, 00:00',
                'testgroupweekly1rangeend' => '2022/09/18, 23:59',
                'testgroupweekly2rangestart' => '2022/09/13, 00:00',
                'testgroupweekly2rangeend' => '2022/09/19, 23:59',
                'testgroupweekly1eventstart' => '12/09/22, 17:00',
                'testgroupweekly1eventend' => '18:00',
                'testgroupweekly1count' => '4',
                'testgroupweekly2eventstart' => '13/09/22, 17:00',
                'testgroupweekly2eventend' => '18:00',
                'testgroupweekly2count' => '4']),
            array(3, [
                'testgroupweekly1rangestart' => '2022/09/19, 00:00',
                'testgroupweekly1rangeend' => '2022/09/25, 23:59',
                'testgroupweekly2rangestart' => '2022/09/20, 00:00',
                'testgroupweekly2rangeend' => '2022/09/26, 23:59',
                'testgroupweekly1eventstart' => '20/09/22, 17:00',
                'testgroupweekly1eventend' => '19:00',
                'testgroupweekly1count' => '1',
                'testgroupweekly2eventstart' => '22/09/22, 17:00',
                'testgroupweekly2eventend' => '18:00',
                'testgroupweekly2count' => '1']),
            array(5, [
                'testgroupweekly1rangestart' => '2022/09/26, 00:00',
                'testgroupweekly1rangeend' => '2022/10/02, 23:59',
                'testgroupweekly2rangestart' => '2022/09/27, 00:00',
                'testgroupweekly2rangeend' => '2022/10/03, 23:59',
                'testgroupweekly1eventstart' => '28/09/22, 17:00',
                'testgroupweekly1eventend' => '18:00',
                'testgroupweekly1count' => '1',
                'testgroupweekly2eventstart' => '1/10/22, 18:00',
                'testgroupweekly2eventend' => '19:00',
                'testgroupweekly2count' => '1']),
            array(6, [
                'testgroupweekly1rangestart' => '2022/10/03, 00:00',
                'testgroupweekly1rangeend' => '2022/10/09, 23:59',
                'testgroupweekly2rangestart' => '2022/10/04, 00:00',
                'testgroupweekly2rangeend' => '2022/10/10, 23:59',
                'testgroupweekly1eventstart' => '5/10/22, 17:00',
                'testgroupweekly1eventend' => '18:00',
                'testgroupweekly1count' => '1',
                'testgroupweekly2eventstart' => '7/10/22, 18:00',
                'testgroupweekly2eventend' => '19:00',
                'testgroupweekly2count' => '1']),
        );
    }

    /**
     * Get events for daily teaching interval (has excluded sections, no reading weeks).
     * @param int $cmid course module id
     * @param array $expectedresult
     * @dataProvider provider_weekly_sections
     */
    public function test_daterange_and_events_for_weekly_sections(int $cmid, array $expectedresult) {
        global $DB;
        $cm = 'testcourseweeklycm'. $cmid;

        $context = \context_module::instance($this->{$cm}->id);
        $cminfo = get_course_and_cm_from_cmid($this->{$cm}->id);

        $groupdata = data_manager::get_user_groups_and_event_type($cminfo[1], $this->testcourseweekly->id, $context);
        $groupdata->eventtype = ['group'];
        $instance = $DB->get_record('timetableevents', ['id' => $this->{$cm}->instance]);

        // Get cached date ranges.
        $cminfo = get_fast_modinfo($this->testcourseweekly->id);
        $daterange = $cminfo->get_cm($this->{$cm}->id)->customdata;
        $groups = [];
        $groups[1] = $this->testgroupweekly1;
        $groups[2] = $this->testgroupweekly2;

        // Get events and check the correct ones have been returned.
        foreach ($groups as $key => $group) {
            $this->assertEquals($expectedresult['testgroupweekly' . $key . 'rangestart'], $daterange[$group->id]['start']);
            $this->assertEquals($expectedresult['testgroupweekly' . $key . 'rangeend'], $daterange[$group->id]['end']);

            $events = $this->datamanager::get_events($instance, $daterange, $groupdata, $group->id);
            $this->assertEquals($expectedresult['testgroupweekly' . $key . 'eventstart'], $events[0]->timestart);
            $this->assertEquals($expectedresult['testgroupweekly' . $key . 'eventend'], $events[0]->timeend);
            $this->assertCount($expectedresult['testgroupweekly' . $key . 'count'], $events);
        }
    }

    /**
     * Data provider for test_daterange_and_events_for_daily_sections.
     * Excluded - cm 2 (section 6), cm 4 (section 8).
     */
    public function provider_daily_sections(): array {
        return array(
            array(1, [
                'testgroupdaily1rangestart' => '2022/09/12, 00:00',
                'testgroupdaily1rangeend' => '2022/09/12, 23:59',
                'testgroupdaily2rangestart' => '2022/09/13, 00:00',
                'testgroupdaily2rangeend' => '2022/09/13, 23:59',
                'testgroupdaily1eventstart' => '12/09/22, 17:00',
                'testgroupdaily1eventend' => '18:00',
                'testgroupdaily1count' => '1',
                'testgroupdaily2eventstart' => '13/09/22, 17:00',
                'testgroupdaily2eventend' => '18:00',
                'testgroupdaily2count' => '1']),
            array(3, [
                'testgroupdaily1rangestart' => '2022/09/13, 00:00',
                'testgroupdaily1rangeend' => '2022/09/13, 23:59',
                'testgroupdaily2rangestart' => '2022/09/14, 00:00',
                'testgroupdaily2rangeend' => '2022/09/14, 23:59',
                'testgroupdaily1eventstart' => '13/09/22, 16:00',
                'testgroupdaily1eventend' => '17:00',
                'testgroupdaily1count' => '1',
                'testgroupdaily2eventstart' => '14/09/22, 18:00',
                'testgroupdaily2eventend' => '20:00',
                'testgroupdaily2count' => '1']),
            array(5, [
                'testgroupdaily1rangestart' => '2022/09/14, 00:00',
                'testgroupdaily1rangeend' => '2022/09/14, 23:59',
                'testgroupdaily2rangestart' => '2022/09/15, 00:00',
                'testgroupdaily2rangeend' => '2022/09/15, 23:59',
                'testgroupdaily1eventstart' => '14/09/22, 17:00',
                'testgroupdaily1eventend' => '19:00',
                'testgroupdaily1count' => '1',
                'testgroupdaily2eventstart' => '15/09/22, 17:00',
                'testgroupdaily2eventend' => '19:00',
                'testgroupdaily2count' => '1']),
            array(6, [
                'testgroupdaily1rangestart' => '2022/09/15, 00:00',
                'testgroupdaily1rangeend' => '2022/09/15, 23:59',
                'testgroupdaily2rangestart' => '2022/09/16, 00:00',
                'testgroupdaily2rangeend' => '2022/09/16, 23:59',
                'testgroupdaily1eventstart' => '15/09/22, 16:00',
                'testgroupdaily1eventend' => '18:00',
                'testgroupdaily1count' => '1',
                'testgroupdaily2eventstart' => '16/09/22, 17:00',
                'testgroupdaily2eventend' => '19:00',
                'testgroupdaily2count' => '1']),
        );
    }

    /**
     * Get events for daily teaching interval (has excluded sections, no reading weeks).
     * @param int $cmid course module id
     * @param array $expectedresult
     * @dataProvider provider_daily_sections
     */
    public function test_daterange_and_events_for_daily_sections(int $cmid, array $expectedresult) {
        global $DB;
        $cm = 'testcoursedailycm'. $cmid;
        $context = \context_module::instance($this->{$cm}->id);
        $cminfo = get_course_and_cm_from_cmid($this->{$cm}->id);

        $groupdata = data_manager::get_user_groups_and_event_type($cminfo[1], $this->testcoursedaily->id, $context);
        $groupdata->eventtype = ['group'];
        $instance = $DB->get_record('timetableevents', ['id' => $this->{$cm}->instance]);

        // Get cached date ranges.
        $cminfo = get_fast_modinfo($this->testcoursedaily->id);
        $daterange = $cminfo->get_cm($this->{$cm}->id)->customdata;
        $groups = [];
        $groups[1] = $this->testgroupdaily1;
        $groups[2] = $this->testgroupdaily2;

        // Get events and check the correct ones have been returned.
        foreach ($groups as $key => $group) {
            $this->assertEquals($expectedresult['testgroupdaily' . $key . 'rangestart'], $daterange[$group->id]['start']);
            $this->assertEquals($expectedresult['testgroupdaily' . $key . 'rangeend'], $daterange[$group->id]['end']);

            $events = $this->datamanager::get_events($instance, $daterange, $groupdata, $group->id);
            $this->assertEquals($expectedresult['testgroupdaily' . $key . 'eventstart'], $events[0]->timestart);
            $this->assertEquals($expectedresult['testgroupdaily' . $key . 'eventend'], $events[0]->timeend);
            $this->assertCount($expectedresult['testgroupdaily' . $key . 'count'], $events);
        }
    }

    /**
     * Generate test courses depending on the teaching interval.
     * @param int $yearid academic year id.
     * @param array $intervals teaching intervals.
     */
    public function create_courses(int $yearid, array $intervals): void {
        global $DB;

        foreach ($intervals as $intervalkey => $interval) {

            $course = 'testcourse' . $interval;

            $this->$course = $this->getDataGenerator()->create_course(['numsections' => 20], ['createsections' => true]);

            // Set course level settings for the plugin.
            $coursesections = $this->datamanager::get_course_sections($this->$course->id);
            $terms = $DB->get_records('timetableevents_term', ['yearid' => $yearid]);
            $terms = array_values($terms);

            $courseobj = new stdClass();
            $courseobj->courseid = $this->$course->id;
            $courseobj->startingtermid = $terms[0]->id;
            $courseobj->teachingstartdate = $terms[0]->startdate;
            foreach ($coursesections as $key => $section) {
                if ($section->section == 5) {
                    $courseobj->firstsection = $key;
                }
            }
            $courseobj->teachinginverval = $intervalkey;
            $courseobj->footertext = 'Course footer text';
            $DB->insert_record('timetableevents_course', $courseobj);

            // Set section overrides.
            $data = new stdClass();
            $data->course = $this->$course->id;
            $data->excluded = [];
            foreach ($coursesections as $key => $section) {
                if ($section->section == 6 || $section->section == 8) {
                    $data->excluded[] = $section->id;
                }
            }

            $data->readingweek = [];
            if ($interval == 'fortnightly') {
                foreach ($coursesections as $key => $section) {
                    if ($section->section == 10) {
                        $data->readingweek[] = $section->id;
                    }
                }
            }

            $data->teachinginverval = $courseobj->teachinginverval;

            $sectionobjs = $this->datamanager::create_section_objects($data);
            foreach ($sectionobjs as $sectionobj) {
                $section = $DB->get_record('timetableevents_section', ['sectionid' => $sectionobj->sectionid]);
                if (!$section) {
                    $DB->insert_record('timetableevents_section', $sectionobj);
                } else {
                    $sectionobj->id = $section->id;
                    $DB->update_record('timetableevents_section', $sectionobj);
                }
            }
        }
    }


    /**
     * Generate test groups depending on the teaching interval and set group overrides.
     * @param array $intervals teaching intervals.
     */
    public function create_groups(array $intervals): void {
        global $DB;
        foreach ($intervals as $interval) {
            $course = 'testcourse'. $interval;
            $courseconfig = 'testcourse'. $interval . 'courseconfig';
            $courseconfig = $this->$courseconfig;
            $teachingstartdate = $courseconfig->teachingstartdate;
            for ($x = 1; $x <= 2; $x++) {
                $group = 'testgroup' . $interval . $x;
                $this->$group = $this->getDataGenerator()->create_group(['courseid' => $this->{$course}->id]);

                // Create group override.
                $data = new stdClass();
                $data->groupid = $this->$group->id;
                $data->startingtermid = $courseconfig->startingtermid;
                $data->teachingstartdate = $teachingstartdate;
                $DB->insert_record('timetableevents_group', $data);

                $teachingstartdate = $teachingstartdate + 86400;
            }
        }
    }

    /**
     * Generate test modules in the right section of a course, depending on the teaching interval.
     * @param array $intervals teaching intervals.
     */
    public function create_modules(array $intervals): void {

        foreach ($intervals as $interval) {

            $course = 'testcourse' . $interval;
            $module = $course;

            $module1 = $this->getDataGenerator()->create_module('timetableevents',
                ['course' => $this->$course->id, 'section' => 5]);
            $module .= 'cm1';
            $this->$module = get_coursemodule_from_id('timetableevents', $module1->cmid);

            // Excluded.
            $module2 = $this->getDataGenerator()->create_module('timetableevents',
                ['course' => $this->$course->id, 'section' => 6]);
            $module = substr_replace($module, 'cm2', -3);
            $this->$module = get_coursemodule_from_id('timetableevents', $module2->cmid);

            $module3 = $this->getDataGenerator()->create_module('timetableevents',
                ['course' => $this->$course->id, 'section' => 7]);
            $module = substr_replace($module, 'cm3', -3);
            $this->$module = get_coursemodule_from_id('timetableevents', $module3->cmid);
            // Excluded.
            $module4 = $this->getDataGenerator()->create_module('timetableevents',
                ['course' => $this->$course->id, 'section' => 8]);
            $module = substr_replace($module, 'cm4', -3);
            $this->$module = get_coursemodule_from_id('timetableevents', $module4->cmid);

            $module5 = $this->getDataGenerator()->create_module('timetableevents',
                ['course' => $this->$course->id, 'section' => 9]);
            $module = substr_replace($module, 'cm5', -3);
            $this->$module = get_coursemodule_from_id('timetableevents', $module5->cmid);

            // Reading week.
            $module6 = $this->getDataGenerator()->create_module('timetableevents',
                ['course' => $this->$course->id, 'section' => 10]);
            $module = substr_replace($module, 'cm6', -3);
            $this->$module = get_coursemodule_from_id('timetableevents', $module6->cmid);
        }
    }

    /**
     * Get course settings for each course.
     * @param array $intervals teaching intervals.
     */
    public function get_course_settings(array $intervals): void {

        foreach ($intervals as $interval) {
            $course = 'testcourse' . $interval;
            $config = 'testcourse' . $interval . 'courseconfig';
            $this->$config = $this->datamanager::get_course_config($this->$course->id);
        }
    }

    /**
     * Generate test events depending on the teaching interval.
     * @param array $intervals teaching intervals.
     */
    public function create_events(array $intervals) {
        global $CFG;
        $this->setAdminUser();
        $events = file_get_contents($CFG->dirroot . '/mod/timetableevents/tests/fixtures/events.json');
        $events = json_decode($events);

        foreach ($intervals as $interval) {

            $course = 'testcourse' . $interval;

            foreach ($events as $event) {
                $group = 'testgroup' . $interval . $event->group;
                $event->courseid = $this->$course->id;
                $event->groupid = $this->$group->id;
                $this->getDataGenerator()->create_event($event);
            }
        }
    }

    /**
     * Convert a string to timestamp.
     * @param string $date teaching intervals.     *
     * @return string
     */
    public function convert_string_to_date(string $date): string {
        $date = \DateTime::createFromFormat('Y/m/d, H:i', $date);

        return $date->getTimestamp();
    }
}
