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
 * Renderable config_acadyears
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
 * Acadyears renderer.
 */
class config_acadyears implements \renderable, \templatable {
    /**
     * @var array $context
     */
    private $context;

    /**
     * Construct this renderable.
     *
     * @param string $id The form ID.
     * @param string $name The form name.
     * @param array $terms The configured terms.
     */
    public function __construct(string $id, string $name, array $terms) {
        $this->context = [
            'id' => $id,
            'name' => $name,
        ];

        // Get academic years that have been used so we can prevent them being deleted.
        $yearsinuse = data_manager::get_acadyears_in_use();

        foreach ($terms as $year) {
            foreach ($year as $term) {
                $this->context['acadyears'][$term->yearid]['terms'][$term->termid] = (array)$term;
                $this->context['acadyears'][$term->yearid]['year'] = $term->yearname;
                $this->context['acadyears'][$term->yearid]['yearid'] = $term->yearid;
                if (!array_key_exists($term->yearid, $yearsinuse)) {
                    $this->context['acadyears'][$term->yearid]['notinuse'] = true;
                } else {
                    $this->context['acadyears'][$term->yearid]['inuse'] = true;
                }
            }
        }

        if (isset($this->context['acadyears'])) {
            // Reset keys so the template picks them up.
            $this->context['acadyears'] = array_values($this->context['acadyears']);

            // Reset keys so the template picks them up.
            foreach ($this->context['acadyears'] as $key => $year) {
                $this->context['acadyears'][$key]['terms'] = array_values($year['terms']);
            }
        } else {
            $this->context['acadyears'] = '';
        }
    }

    /**
     * Return context for the template.
     *
     * @param renderer_base $output
     *
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        return $this->context;
    }
}
