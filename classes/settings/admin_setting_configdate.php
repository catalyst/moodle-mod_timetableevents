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
 * An admin setting for a date input.
 *
 * @package    mod_timetableevents
 * @copyright  2022 onwards University College London {@link https://www.ucl.ac.uk/}
 * @copyright  2022 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */

namespace mod_timetableevents\settings;

use admin_setting_configtext;
use mod_timetableevents\data_manager;

/**
 * An admin setting for a date input.
 */
class admin_setting_configdate extends admin_setting_configtext {

    /**
     * Save a setting
     *
     * @param string $data
     * @return string empty or error string
     */
    public function write_setting($data) {
        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }

        $data = new \DateTime($data);
        $data = $data->getTimestamp();

        return parent::write_setting($data);
    }

    /**
     * Validate the submitted data of the setting.
     *
     * @param string $data Value submitted for the field
     * @return true|string True if validation is ok, otherwise string containing error message.
     */
    public function validate($data) {

        // Date must be set.
        if ($data === '') {
            return get_string('required');
        }

        return true;

    }

    /**
     * Return the HTML output for the field.
     * @param int $data form data
     * @param string $query
     *
     * @return string
     */
    public function output_html($data, $query = ''): string {

        global $OUTPUT;

        $default = $this->get_defaultsetting();
        $context = (object) [
            'size' => $this->size,
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'value' => $data,
            'forceltr' => $this->get_force_ltr(),
            'readonly' => $this->is_readonly(),
        ];

        if (!$data) {
            $currentyear = get_config('mod_timetableevents', 'currentacadyear');
            if ($currentyear) {
                $years = data_manager::get_terms($currentyear);
                $currentyear = get_config('mod_timetableevents', 'currentacadyear');

                if ($years) {
                    $terms = $years[$currentyear];
                    $terms = array_values($terms);

                    $startdate = \DateTime::createFromFormat('U', $terms[0]->startdate);
                    $startdate = date('Y-m-d', $startdate->getTimestamp());
                    $context->value = $startdate;

                }
            }
        } else {
            $startdate = \DateTime::createFromFormat('U', $data);
            $startdate = date('Y-m-d', $startdate->getTimestamp());
            $context->value = $startdate;
        }

        $element = $OUTPUT->render_from_template('mod_timetableevents/configdate', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);

    }

}
