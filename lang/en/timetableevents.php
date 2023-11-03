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
$string['coursesetting:academicyear_help'] = 'The academic year for which events will be shown. Will display the current site default if not changed here. Defined at site level by site administrators.';
$string['coursesetting:academicyear:notconfigured'] = 'Academic years have not been set up. Please contact your site administrator.';
$string['coursesetting:academicyear:notset'] = 'Current academic year has not been set.';
$string['coursesetting:excludedsections'] = 'Excluded sections';
$string['coursesetting:excludedsections_help'] = 'Sections which are \'skipped\' in the calculation of teaching intervals – these sections will not be counted and will not display any events. The teaching interval in the sections before and after an excluded section will be sequential as if the excluded section did not exist.';
$string['coursesetting:firstsection'] = 'First teaching section';
$string['coursesetting:firstsection_help'] = 'The first section in which events based on the settings defined at course level will be shown. This defaults to whatever site default is defined but can be overridden to any section within this course.';
$string['coursesetting:footertext'] = 'Additional footer text';
$string['coursesetting:footertext_help'] = 'This setting can be used to display messages to users on every timetable instance within sections. The message displayed will be in addition to any site-wide message displayed in footers.';
$string['coursesetting:groupoverride'] = 'x {$a->name} - Starting term: {$a->term}, Teaching start date: {$a->teachingstartdate}';
$string['coursesetting:groupoverridesoracadyears'] = 'Groups or academic years have not been set up';
$string['coursesetting:groupoverridesoracadyears_help'] = 'Used to define different teaching schedules for different groups – this setting is used when there are groups that are taught at different times to allow students and teachers in those groups to see the correct timetable information within sections. Groups without overrides will use the course settings defined above.';
$string['coursesetting:groupoverrides'] = 'Group overrides';
$string['coursesetting:groupoverrides_help'] = 'The group for which the current override is being defined.';
$string['coursesetting:groupoverride:add'] = 'Add group override';
$string['coursesetting:groupoverride:term'] = 'Term {$a->termno}';
$string['coursesetting:groupoverride:notconfigured'] = 'Either groups have not been set up or they have already been overridden';
$string['coursesetting:settings'] = 'Timetable events settings';
$string['coursesetting:readingweeks'] = 'Reading weeks';
$string['coursesetting:readingweeks_help'] = 'Sections which run for 1 week in the calculation of teaching intervals – only available for courses that use fortnightly teaching intervals.';
$string['coursesetting:teachingstartdate'] = 'Teaching start date';
$string['coursesetting:teachingstartdate_help'] = 'The date of the first event. This setting only needs to be used if the start date is either before or after the default defined within the \'Starting term\' setting above. If teaching starts within the first week of the relevant term this setting should be left unchanged.';
$string['coursesetting:teachingstartdate:group'] = 'Teaching start date';
$string['coursesetting:teachingstartdate:group_help'] = 'The date of the first event for the group for which the override is being defined. This setting only needs to be used if the start date is either before or after the first week of a term defined within the \'Starting Term\' setting above.';
$string['coursesetting:teachinginverval'] = 'Teaching interval';
$string['coursesetting:teachinginverval_help'] = 'The frequency of the events being shown within sections. Which section shows which time interval is based on the settings above.
<ul>
    <li>Weekly: Timetable Event instances in a given section will display all events scheduled within the appropriate week.</li>
    <li>Fortnightly: Timetable Event instances in a given section will display all events / live sessions for the appropriate two weeks.</li>
    <li>Daily: Timetable Event instances in a given section will display all events scheduled on the appropriate day.</li>
</ul>';
$string['coursesetting:teachinginverval:daily'] = 'Daily';
$string['coursesetting:teachinginverval:weekly'] = 'Weekly';
$string['coursesetting:teachinginverval:fortnightly'] = 'Fortnightly';
$string['coursesetting:term'] = 'Starting term';
$string['coursesetting:term_help'] = 'The term when the course events are to start. Events before this date will not appear in sections that use course defaults in an activity instance.';
$string['coursesetting:term:options'] = 'Term {$a->termno} {$a->startdate} - {$a->enddate}';
$string['coursesetting:term:group'] = 'Starting term';
$string['coursesetting:term:group_help'] = 'The term in which events for the group start. Events for this group scheduled before the beginning of the starting term will not be displayed within the course’s sections.';
$string['footertextadmin'] = 'Showing events for: {$a->coursename} - {$a->group}{$a->daterange}';
$string['modsetting:coursedefaults'] = 'Display events based on course defaults';
$string['modsetting:coursedefaults_help'] = 'If this box is checked, the teaching interval calculation defined in the course admin settings will be used to determine what information is to be displayed to users. Uncheck if you need to override the course settings for this instance.';
$string['modsetting:coursesearch'] = 'Course to display events from';
$string['modsetting:coursesearch_help'] = 'By default, events are shown only for the current course. This setting can be used to display events from a different course. Note that a group needs to be selected from the defined course below if this setting is used to define a course other than the current one.';
$string['modsetting:daterange'] = 'Date range';
$string['modsetting:daterange_help'] = 'This setting is used to override the date calculation to show events within this instance of the timetable events display. If not enabled, the course-level settings will be used to determine which date range is used. If enabled, all events within the defined range will be displayed within this instance.';
$string['modsetting:groupsearch'] = 'Group to display events from selected course';
$string['modsetting:groupsearch_help'] = 'This setting is only used if the course for which events are to be displayed (see \'Course to display events from\' setting above) has been set to display events from a different course. This setting is used to select which group to show events for in the defined course. Only groups that have associated timetabled events will be available for selection.';
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
$string['pluginsettings:createacadyear'] = 'You must create at least 1 academic year to continue configuring this plugin.';
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
$string['vieweventsforgroup'] = 'View events for group';
