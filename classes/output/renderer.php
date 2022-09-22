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
 * Timetable events renderer
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\output;

/**
 * Render methods for timetable events.
 */
class renderer extends \plugin_renderer_base {

    /**
     * Basic renderer for the location templatable.
     *
     * This is just the default behaviour for templatables passed to render(), but defining it here allows it to be overridden.
     *
     * @param \templatable $location
     * @return string
     * @throws \moodle_exception
     */
    public function render_location(\templatable $location) : string {
        return $this->render_from_template('mod_timetableevents/location', $location->export_for_template($this->output));
    }

    /**
     * Basic renderer for the display templatable.
     *
     * This is just the default behaviour for templatables passed to render(), but defining it here allows it to be overridden.
     * @param \templatable $display
     * @return string
     * @throws \moodle_exception
     */
    public function render_display(\templatable $display) : string {
        return $this->render_from_template('mod_timetableevents/display', $display->export_for_template($this->output));
    }
}
