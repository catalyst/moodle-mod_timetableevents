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
 * Language strings for en
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['acadyears:inuse'] = 'In use';
$string['cachedef_course_instance_events'] = 'Cached events';
$string['cachedef_course_instance_dateranges'] = 'Cached instance settings';
$string['coursesetting:addacademicyear'] = 'Add new academic year';
$string['coursesetting:academicyear'] = 'Academic year';
$string['coursesetting:academicyear:notconfigured'] = 'Academic years have not been set up. Please contact your site administrator.';
$string['coursesetting:academicyear:notset'] = 'Current academic year has not been set.';
$string['coursesetting:excludedsections'] = 'Excluded sections';
$string['coursesetting:firstsection'] = 'First teaching section';
$string['coursesetting:footertext'] = 'Additional footer text';
$string['coursesetting:groupoverride'] = '{$a->name} - Starting term: {$a->term}, Teaching start date: {$a->teachingstartdate}';
$string['coursesetting:groupoverridesoracadyears'] = 'Groups or academic years have not been set up';
$string['coursesetting:groupoverrides'] = 'Group overrides';
$string['coursesetting:groupoverride:add'] = 'Add group override';
$string['coursesetting:groupoverride:term'] = 'Term {$a->termno}';
$string['coursesetting:groupoverride:notconfigured'] = 'Either groups have not been set up or they have already been overridden';
$string['coursesetting:settings'] = 'Timetable events settings';
$string['coursesetting:readingweeks'] = 'Reading weeks';
$string['coursesetting:teachingstartdate'] = 'Teaching start date';
$string['coursesetting:teachinginverval'] = 'Teaching interval';
$string['coursesetting:teachinginverval:daily'] = 'Daily';
$string['coursesetting:teachinginverval:weekly'] = 'Weekly';
$string['coursesetting:teachinginverval:fortnightly'] = 'Fortnightly';
$string['coursesetting:term'] = 'Starting term';
$string['coursesetting:term:options'] = 'Term {$a->termno} {$a->startdate} - {$a->enddate}';
$string['footertextadmin'] = 'Showing events for: {$a->coursename} - {$a->group} {$a->daterange}';
$string['modsetting:coursedefaults'] = 'Display events based on course defaults';
$string['modsetting:coursesearch'] = 'Course to display events from';
$string['modsetting:daterange'] = 'Date range';
$string['modsetting:groupsearch'] = 'Group to display events from selected course';
$string['modsetting:nogroups'] = 'Groups are not set up for this course';
$string['modsetting:placeholder'] = 'Start typing to search';
$string['footertext'] = 'Footer text';
$string['footertext_desc'] = 'This text will be displayed after each instance of the timetable events module on a course page. Additional text can be added in course settings.';
$string['invalidcourseshortname'] = 'The course shortname provided did not match any courses.';
$string['invalidgroupidnumber'] = 'The group idnumber provided did not match any groups.';
$string['invalidtime'] = 'An invalid time was provided. You must provide a date and time in ISO 8601 format. {$a}';
$string['invalidtimeend'] = 'The timeend for an event must be after timestart.';
$string['modulename'] = 'Timetable events';
$string['modulenameplural'] = 'Timetable events';
$string['notconfigured'] = 'Site settings have not been configured. Please contact your administrator.';
$string['nodaterange'] = 'No date range available for this section';
$string['nogroupselected'] = 'No group selected';
$string['pluginadministration'] = 'Timetable events';
$string['pluginname'] = 'Timetable events';
$string['pluginsettings'] = 'Plugin settings';
$string['pluginsettings:acadyears'] = 'Academic years';
$string['pluginsettings:acadyears:edit'] = 'Timetable events - Edit academic year';
$string['pluginsettings:acadyears:name'] = 'Name';
$string['pluginsettings:acadyears:termend'] = 'Term {no} end';
$string['pluginsettings:acadyears:termstart'] = 'Term {no} start';
$string['pluginsettings:currentacadyear'] = 'Current academic year';
$string['pluginsettings:firstteachingsection'] = 'First teaching section';
$string['pluginsettings:firstteachingsection_desc'] = 'This will be applied to all new courses unless overridden in the course settings.';
$string['pluginsettings:currentacadyear_desc'] = 'This will be applied to all new courses unless overridden in the course settings.';
$string['pluginsettings:teachinginterval'] = 'Teaching interval';
$string['pluginsettings:teachinginterval_desc'] = 'This will be applied to all new courses unless overridden in the course settings.';
$string['pluginsettings:teachingstartdate'] = 'Teaching start date';
$string['pluginsettings:teachingstartdate_desc'] = 'This will be applied to all new courses unless overridden in the course settings. Defaults to start date of first term';
$string['pluginsettings:year'] = 'Year';
$string['pluginsettings:term'] = 'Term';
$string['privacy:metadata'] = 'The plugin stores no personal data. All events are stored using the core Calendar API, and all other
        data is anonymous settings.';
$string['section'] = 'Section';
$string['timetableevents:addinstance'] = 'Add a timetable events instance.';
$string['timetableevents:import'] = 'Import timetable events via the web service.';
$string['timetableevents:view'] = 'View a timetable events instance.';
$string['timetableevents:viewall'] = 'View all timetable events instances.';
