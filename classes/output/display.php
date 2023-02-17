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
 * Renderable location
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\output;

use cm_info;
use mod_timetableevents\data_manager;
use renderer_base;
use stdClass;

/**
 * Location renderer.
 */
class display implements \renderable, \templatable {

    /**
     * @var array $context
     */
    private $context;

    /**
     * Construct this renderable.
     *
     * @param int $courseid The course id
     * @param cm_info $cm The course module
     * @param array $siteconfig The site config settings
     * @param stdClass $courseconfig The course config settings
     * @param array $daterange The course config settings
     */
    public function __construct(int $courseid, cm_info $cm, array $siteconfig,
                                stdClass $courseconfig, array $daterange) {
        global $DB, $OUTPUT, $USER, $SESSION;

        $this->context = [
            'courseid' => $courseid,
            'cm' => $cm,
            'siteconfig' => $siteconfig,
            'courseconfig' => $courseconfig,
            'daterange' => $daterange,
        ];

        $instance = $DB->get_record('timetableevents', ['id' => $cm->instance]);
        $context = \context_module::instance($cm->id);
        $grouppreference = get_user_preferences('mod_timetableevents_' . $courseid, null,  $USER->id);

        // If the current section is greater or equal to the first teaching section.
        if ($cm->sectionnum >= $courseconfig->firstsection) {

            $this->context['icon'] = $OUTPUT->pix_icon(
                'course', 'theme', 'mod_timetableevents', ['class' => 'mod_timetableevents_course_icon']
            );

            $groupdata = data_manager::get_user_groups_and_event_type($cm, $courseid, $context);
            // Check or edit user group preference.
            $grouppreference = data_manager::check_and_update_group_preference($grouppreference, $groupdata->groups, $courseid);

            if (isset($daterange)) {
                $events = data_manager::get_events($instance, $daterange, $groupdata, $grouppreference);

                // Show a group dropdown to filter if we are only showing events for this course
                // and the user has access to more than one group.
                if (is_null($instance->groupid) && (is_null($instance->courseoverride) || $instance->courseoverride == $courseid)
                    && $cm->groupmode != 0) {
                    if (count($groupdata->groups) > 1) {
                        $this->context['editor'] = 1;
                        $options = [];
                        foreach ($groupdata->groups as $key => $group) {
                            $options[$key] = $group;
                        }

                        $referrer = $SESSION->fromdiscussion;
                        $url = new \moodle_url('/mod/timetableevents/group_preference.php',
                            ['course' => $courseid, 'cm' => $cm->id, 'sesskey' => sesskey(), 'referrer' => $referrer]);
                        $select = $OUTPUT->single_select($url, 'mod_timetableevents-select-groups',
                            $options, $grouppreference, null, 'mod_timetableevents-select-groups' . $cm->id,
                            ['class' => 'mod_timetableevents-select-groups']);
                        $this->context['select'] = $select;
                    }
                }

                // If we have some events, add them to the template context.
                if (count($events) > 0) {
                    $returnevents = [];
                    foreach ($events as $event) {
                        $returnevents[] = $event;
                    }
                    $this->context['events'] = $returnevents;
                    $this->context['footertext'] = $siteconfig['footertext'] . ' ' . $courseconfig->footertext;
                } else {
                    $this->context['noevents'] = 1;
                }

            } else {
                $this->context['noevents'] = 1;
            }
        } else {
            $this->context['noevents'] = 1;
        }

        // If the user has the viewall capability, show details about what has been configured for the instance
        // to help with troubleshooting.
        if (has_capability('mod/timetableevents:viewall', $context)) {

            if ($instance->courseoverride !== null && $instance->courseoverride != $instance->course) {
                $course = get_course($instance->courseoverride);
            } else {
                $course = get_course($instance->course);
            }
            $groupid = 0;
            $groupname = "";
            if ($grouppreference) {
                $group = groups_get_group($grouppreference);
                $groupid = $grouppreference;
                $groupname = "Group " . $group->name . " - ";
            }

            if (!is_null($instance->groupid)) {
                $group = groups_get_group($instance->groupid);
                $groupid = $group->id;
                $groupname = "Group " . $group->name . " - ";
            }

            if (isset($groupdata->eventtype) && in_array('course', $groupdata->eventtype)) {
                $groupid = $group->id;
                $groupname = "";
            }

            $daterangestring = get_string('nodaterange', 'timetableevents');

            if ($daterange[$groupid]) {
                $daterangestring = $daterange[$groupid]['start'] . ' - ' . $daterange[$groupid]['end'];
            }

            $this->context['footertextadmin'] = get_string('footertextadmin', 'timetableevents',
                ['coursename' => $course->fullname, 'group' => $groupname, 'daterange' => $daterangestring]
            );
        }
    }

    /**
     * Return location and URL for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) : array {
        return $this->context;
    }
}
