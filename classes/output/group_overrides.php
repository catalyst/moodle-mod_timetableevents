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
 * Renderable groupoverrides
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\output;

use mod_timetableevents\data_manager;
use renderer_base;

/**
 * Group overrides renderer.
 */
class group_overrides implements \renderable, \templatable {
    /**
     * @var array $groupoverrides
     */
    private $groupoverrides;

    /**
     * Construct this renderable.
     *
     * @param int $courseid The location
     */
    public function __construct(int $courseid) {
        global $DB;
        $groupoverrides = $DB->get_records_sql(
            "SELECT tg.id, g.id AS groupid, tg.startingtermid, tg.teachingstartdate, g.name, tt.yearid
               FROM {timetableevents_group} tg
               JOIN {timetableevents_term} tt ON tt.id = tg.startingtermid
               JOIN {groups} g ON tg.groupid = g.id
               JOIN {course} c ON c.id = g.courseid
              WHERE c.id = ?",
            [$courseid]
        );

        $this->groupoverrides = [];
        $terms = data_manager::get_terms();
        $tz = \core_date::get_server_timezone_object();

        foreach ($groupoverrides as $groupoverride) {
            $startdate = \DateTime::createFromFormat('U', $groupoverride->teachingstartdate, $tz);
            $teachingstartdate = date('d/m/Y', $startdate->getTimestamp());

            $strparams = [];
            $strparams['name'] = $groupoverride->name;
            $strparams['term'] = $terms[$groupoverride->yearid][$groupoverride->startingtermid]->termno;
            $strparams['teachingstartdate'] = $teachingstartdate;

            $this->groupoverrides[$groupoverride->groupid] = [
                'label' => get_string('coursesetting:groupoverride', 'mod_timetableevents', $strparams),
                'value' => $groupoverride->id,
                'groupid' => $groupoverride->groupid,
            ];
        }
    }

    /**
     * Return group overrides for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        return array_values($this->groupoverrides);
    }

    /**
     * Return group overrides.
     *
     * @return array
     */
    public function get_group_overrides(): array {
        return $this->groupoverrides;
    }
}
