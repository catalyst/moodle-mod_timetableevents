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
 * Upgrade function for mod_timetableevents
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for mod_timetableevents
 *
 * @param int $oldversion The version we are upgrading from
 */
function xmldb_timetableevents_upgrade(int $oldversion = 0) : bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022081600) {

        // Define fields to be added to timetableevents.
        $table = new xmldb_table('timetableevents');

        $fields = [
            new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id'),
            new xmldb_field('courseoverride', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'course'),
            new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'courseoverride'),
            new xmldb_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name'),
            new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'intro'),
            new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'introformat'),
            new xmldb_field('startdate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'groupid'),
            new xmldb_field('enddate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'startdate'),
            new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'enddate'),
            new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified'),
            new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated')
        ];

        $keys = [
            new xmldb_key('course', XMLDB_KEY_FOREIGN, ['course'], 'course', ['id']),
            new xmldb_key('groupid', XMLDB_KEY_FOREIGN, ['groupid'], 'groups', ['id']),
            new xmldb_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id'])
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        foreach ($keys as $key) {
            $dbman->add_key($table, $key);
        }

        // Define table timetableevents_course to be created.
        $table = new xmldb_table('timetableevents_course');

        // Adding fields to table timetableevents_course.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startingtermid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('teachingstartdate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('firstsection', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('teachinginverval', XMLDB_TYPE_INTEGER, '7', null, XMLDB_NOTNULL, null, null);
        $table->add_field('footertext', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table timetableevents_course.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN_UNIQUE, ['courseid'], 'course', ['id']);
        $table->add_key('firstsection', XMLDB_KEY_FOREIGN_UNIQUE, ['firstsection'], 'section', ['id']);
        $table->add_key('startingtermid', XMLDB_KEY_FOREIGN, ['startingtermid'], 'timetableevents_term', ['id']);

        // Conditionally launch create table for timetableevents_course.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table timetableevents_section to be created.
        $table = new xmldb_table('timetableevents_section');

        // Adding fields to table timetableevents_section.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('excluded', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('readingweek', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table timetableevents_section.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('sectionid', XMLDB_KEY_FOREIGN_UNIQUE, ['sectionid'], 'sections', ['id']);

        // Conditionally launch create table for timetableevents_section.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table timetableevents_group to be created.
        $table = new xmldb_table('timetableevents_group');

        // Adding fields to table timetableevents_group.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startingtermid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('teachingstartdate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table timetableevents_group.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('groupid', XMLDB_KEY_FOREIGN_UNIQUE, ['groupid'], 'groups', ['id']);
        $table->add_key('startingtermid', XMLDB_KEY_FOREIGN, ['startingtermid'], 'timetableevents_term', ['id']);

        // Conditionally launch create table for timetableevents_group.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table timetableevents_year to be created.
        $table = new xmldb_table('timetableevents_year');

        // Adding fields to table timetableevents_year.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table timetableevents_year.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for timetableevents_year.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table timetableevents_term to be created.
        $table = new xmldb_table('timetableevents_term');

        // Adding fields to table timetableevents_term.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('yearid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table timetableevents_term.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('yearid', XMLDB_KEY_FOREIGN, ['yearid'], 'timetableevents_year', ['id']);

        // Conditionally launch create table for timetableevents_term.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Timetableevents savepoint reached.
        upgrade_mod_savepoint(true, 2022081600, 'timetableevents');
    }

    if ($oldversion < 2022102100) {

        // Define key firstsection (foreign-unique) to be dropped form timetableevents_course.
        $table = new xmldb_table('timetableevents_course');
        $key = new xmldb_key('firstsection', XMLDB_KEY_FOREIGN_UNIQUE, ['firstsection'], 'section', ['id']);

        // Launch drop key firstsection.
        $dbman->drop_key($table, $key);

        // Timetableevents savepoint reached.
        upgrade_mod_savepoint(true, 2022102100, 'timetableevents');
    }

    return true;
}
