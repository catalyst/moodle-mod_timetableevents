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
 * Class for data management.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents;


/**
 * Class for teaching intervals.
 */
class teaching_intervals {
    /**
     * @var int WEEKLY
     *
     * The value for weekly teaching intervals.
     */
    const WEEKLY = 0;

    /**
     * @var int FORTNIGHTLY
     *
     * The value for fortnightly teaching intervals.
     */
    const FORTNIGHTLY = 1;

    /**
     * @var int DAILY
     *
     * The value for daily teaching intervals.
     */
    const DAILY = 2;
}
