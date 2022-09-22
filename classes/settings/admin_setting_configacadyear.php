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
 * An admin setting for selecting configured lti tools.
 *
 * @package    mod_timetableevents
 * @copyright  2022 onwards University College London {@link https://www.ucl.ac.uk/}
 * @copyright  2022 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Sarah Cotton <sarah.cotton@catalyst-eu.net>
 */

namespace mod_timetableevents\settings;

use admin_setting;
use mod_timetableevents\data_manager;
use mod_timetableevents\output\config_acadyears;

/**
 * An admin setting for selecting and editing academic year and term dates.
 */
class admin_setting_configacadyear extends admin_setting {

    /**
     * Return the HTML output for the field.
     *
     * @param mixed $data parent function parameter
     * @param string $query parent function parameter
     * @return string The HTML element
     */
    public function output_html($data, $query = ''): string {
        global $OUTPUT;

        $default = $this->get_defaultsetting();
        $terms = data_manager::get_terms();
        $id = $this->get_id();
        $name = $this->get_full_name();

        $acadyears = new config_acadyears($id, $name, $terms);
        $context = $acadyears->export_for_template($OUTPUT);
        $element = $OUTPUT->render_from_template('mod_timetableevents/acadyears', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);

    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Must be implemented but isn't required as this setting is only for displaying a table of data.
     * @param string $data string or array, must not be NULL.
     * @return string Always returns ''.
     */
    public function write_setting($data): string {
        return '';
    }
}
