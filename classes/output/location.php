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

use renderer_base;

/**
 * Location renderer.
 */
class location implements \renderable, \templatable {

    /**
     * @var string $location
     */
    private $location;

    /**
     * @param string $url
     */
    private $url;

    public function __construct(string $location, ?string $url = null) {
        $this->location = $location;
        $this->url = $url;
    }

    public function export_for_template(renderer_base $output) : array {
        return [
            'location' => $this->location,
            'url' => $this->url
        ];
    }
}
