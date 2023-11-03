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
 * Behat steps for mod_timetablevents
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps definitions for the timetableevents module.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_timetableevents extends behat_base {
    /**
     * Send events to the mod_timetableevents_import_events web service function.
     *
     * @Given /^I send the timetableevents import web service the following:$/
     * @param \Behat\Gherkin\Node\TableNode $events
     */
    public function i_send_the_timetableevents_import_web_service_the_following(\Behat\Gherkin\Node\TableNode $events): void {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');
        try {
            $args = external_api::validate_parameters(
                mod_timetableevents\external\import_events::execute_parameters(),
                ['events' => $events->getColumnsHash()]
            );
            mod_timetableevents\external\import_events::execute($args['events']);
        } catch (invalid_parameter_exception $e) {
            throw new Exception($e->debuginfo);
        }
    }

    /**
     * Set the current academic year config setting based on the year name.
     *
     * @Given the current timetableevents academic year is set to :name
     * @param string $name
     */
    public function the_timetableevents_academic_year_is_set_to(string $name): void {
        global $DB;
        $yearid = $DB->get_field('timetableevents_year', 'id', ['name' => $name]);
        set_config('currentacadyear', $yearid, 'mod_timetableevents');
    }
}
