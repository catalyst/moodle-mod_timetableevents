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
 * Form class for academic years settings.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\forms;

use mod_timetableevents\data_manager;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');
require_once($CFG->dirroot.'/mod/timetableevents/lib.php');

/**
 * Form class for academic years settings.
 *
 * @package    mod_timetableevents
 * @copyright  2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class acadyears_settings extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('pluginsettings:acadyears:name', 'timetableevents'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addElement('hidden', 'yearid', 0);
        $mform->setType('yearid', PARAM_INT);

        $repeatarray = [
            $mform->createElement('date_selector', 'startdate',
                get_string('pluginsettings:acadyears:termstart', 'timetableevents')),
            $mform->createElement('date_selector', 'enddate',
                get_string('pluginsettings:acadyears:termend', 'timetableevents')),
            $mform->createElement('hidden', 'termid', 0),
        ];

        $mform->setType('termid', PARAM_INT);

        if (isset($this->_customdata['id'])) {
            $acadyear = data_manager::get_terms($this->_customdata['id']);
            $repeatno = count($acadyear[$this->_customdata['id']]);
        } else {
            $repeatno = 1;
        }

        $repeatoptions = [
            'limit' => [
                'default' => $repeatno,
            ],
        ];

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeatoptions,
            'term_repeats',
            'term_add_fields',
            1,
            null,
            true
        );

        if (isset($this->_customdata['edit'])) {
            $mform->addElement('hidden', 'edit', $this->_customdata['edit']);
            $mform->setType('edit', PARAM_INT);
        }

        $this->add_action_buttons();

    }

    // phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * Preprocess incoming data.
     * @param array $default_values default values for form
     * @return array|null
     */
    public function data_preprocessing(array &$default_values): ?array {

        if ($default_values) {
            $termids = array_keys($default_values);
            $terms = array_values($default_values);

            foreach (array_keys($termids) as $key) {
                $values['startdate['.$key.']'] = $terms[$key]->startdate;
                $values['enddate['.$key.']'] = $terms[$key]->enddate;
                $values['termid['.$key.']'] = $termids[$key];
                $values['name'] = $terms[$key]->yearname;
                $values['yearid'] = $terms[$key]->yearid;
            }
            return $values;
        }

        return null;

    }
    // phpcs:enable

}
