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
 * Class for restoring the mod_timetableevents
 *
 * @package    mod_timetableevents
 * @subpackage backup-moodle2
 * @copyright  2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author     Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/timetableevents/backup/moodle2/restore_timetableevents_stepslib.php');

/**
 * Class that provides all the settings and steps to perform one restore of the activity.
 */
class restore_timetableevents_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Time tableevents only has one structure step.
        $this->add_step(new restore_timetableevents_activity_structure_step('timetableevents_structure', 'timetableevents.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('timetableevents',
            array('courseoverride', 'name', 'intro', 'introformat', 'groupid', 'startdate', 'enddate'), 'timetableevents');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        return [];
    }

    /**
     * Define the restore log rules that will be applied
     * when restoring timetableevents logs.
     * It must return one array of objects.
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('timetableevents', 'add', 'view.php?id={course_module}', '{timetableevents}');
        $rules[] = new restore_log_rule('timetableevents', 'update', 'view.php?id={course_module}', '{timetableevents}');
        $rules[] = new restore_log_rule('timetableevents', 'view', 'view.php?id={course_module}', '{timetableevents}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * when restoring course logs. It must return one array
     * of objects.
     *
     * Note these rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        $rules[] = new restore_log_rule('timetableevents', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
