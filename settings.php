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
 * Timetable events settings
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var admin_category $ADMIN
 * @var admin_settingpage $settings
 * @var \core\plugininfo\mod $module
 */

use \mod_timetableevents\settings\admin_setting_configacadyear;
use \mod_timetableevents\settings\admin_setting_configdate;
use \mod_timetableevents\data_manager;

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $category = new admin_category('modsettingtimetableeventscat', new lang_string('pluginname', 'mod_timetableevents'));
    $ADMIN->add('modsettings', $category);

    $settings = new admin_settingpage($section, new lang_string('pluginsettings', 'mod_timetableevents'));
    if ($ADMIN->fulltree) {
        // Terms.
        $settings->add(new admin_setting_configacadyear(
            'mod_timetableevents/acadyears',
            get_string('pluginsettings:acadyears', 'mod_timetableevents'),
            null,
            '',
            true,
            false
        ));

        $acadyears = data_manager::get_acadyears();
        $choices = [0 => 'Not selected'];
        foreach ($acadyears as $acadyear) {
            $choices[$acadyear->id] = $acadyear->name;
        }

        if (empty($acadyears)) {
            $settings->add(
                new admin_setting_heading(
                    'mod_timetableevents/createadacyear',
                    '',
                    new lang_string('pluginsettings:createacadyear', 'mod_timetableevents')
                )
            );
        } else {
            $firstyearid = reset($acadyears)->id;
            // Current academic year setting.
            $settings->add(new admin_setting_configselect('mod_timetableevents/currentacadyear',
                new lang_string('pluginsettings:currentacadyear', 'mod_timetableevents'),
                new lang_string('pluginsettings:currentacadyear_desc', 'mod_timetableevents'),
                $firstyearid,
                $choices
            ));
            $startdate = $DB->get_field('timetableevents_term', 'startdate', ['yearid' => $firstyearid]);
            $startdate = \DateTime::createFromFormat('U', $startdate, core_date::get_server_timezone_object());
            // Teaching start date.
            $settings->add(new admin_setting_configdate('mod_timetableevents/teachingstartdate',
                new lang_string('pluginsettings:teachingstartdate', 'mod_timetableevents'),
                new lang_string('pluginsettings:teachingstartdate_desc', 'mod_timetableevents'),
                    $startdate->format('Y-m-d')
            ));
            // First teaching section.
            $settings->add(new admin_setting_configtext('mod_timetableevents/firstteachingsection',
                new lang_string('pluginsettings:firstteachingsection', 'mod_timetableevents'),
                new lang_string('pluginsettings:firstteachingsection_desc', 'mod_timetableevents'),
                '5'
            ));

            $refl = new ReflectionClass('mod_timetableevents\teaching_intervals');
            $teachingintervals = $refl->getConstants();
            $choices = [];
            foreach ($teachingintervals as $key => $teachinginterval) {
                $choices[$teachinginterval] = ucwords(strtolower($key));
            }

            // Current academic year setting.
            $settings->add(new admin_setting_configselect('mod_timetableevents/teachinginterval',
                new lang_string('pluginsettings:teachinginterval', 'mod_timetableevents'),
                new lang_string('pluginsettings:teachinginterval_desc', 'mod_timetableevents'),
                \mod_timetableevents\teaching_intervals::FORTNIGHTLY,
                $choices
            ));

            // Footer text.
            $settings->add(new admin_setting_configtext('mod_timetableevents/footertext',
                new lang_string('footertext', 'mod_timetableevents'),
                new lang_string('footertext_desc', 'mod_timetableevents'),
                '',
                PARAM_TEXT
            ));
        }

    }

    $ADMIN->add('modsettingtimetableeventscat', $settings);

    // If something else (e.g. the theme) has added a page called mod_timetableevents_extra somewhere in the admin tree, move it
    // under the plugin's settings.
    if ($extra = $ADMIN->locate('mod_timetableevents_extra')) {
        $ADMIN->prune('mod_timetableevents_extra');
        $ADMIN->add('modsettingtimetableeventscat', $extra);
    }
}

$settings = null; // Don't add the standard settings page.
