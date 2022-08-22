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
 * Form class for course settings.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\forms;

use mod_timetableevents\data_manager;
use mod_timetableevents\output\group_overrides;
use mod_timetableevents\teaching_intervals;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');
require_once($CFG->dirroot.'/mod/timetableevents/lib.php');

/**
 * Class for the course settings page.
 *
 * @package    mod_timetableevents
 * @copyright  2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_settings extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $PAGE, $OUTPUT;
        $mform =& $this->_form;

        $mform->disable_form_change_checker();

        $acadyears = data_manager::get_acadyears();
        if ($acadyears) {
            $acadyearoptions = [];
            foreach ($acadyears as $acadyear) {
                $acadyearoptions[$acadyear->id] = $acadyear->name;
            }
            $mform->addElement('select', 'academicyear',
                get_string('coursesetting:academicyear', 'timetableevents'), $acadyearoptions);
            $acadyear = reset($acadyears);
            $mform->setDefault('academicyear', $acadyear->id);
        } else {
            $mform->addElement('static', 'academicyear', get_string('coursesetting:academicyear', 'timetableevents'),
                get_string('coursesetting:academicyear:notconfigured', 'timetableevents'));
        }

        $this->_customdata['terms'] = data_manager::get_terms();
        $mform->addElement('hidden', 'termsjson', json_encode((object) $this->_customdata['terms']));
        $mform->setType('termsjson', PARAM_RAW);

        $mform->addElement('date_selector', 'teachingstartdate', get_string('coursesetting:teachingstartdate', 'timetableevents'));
        $groups = groups_get_course_data($this->_customdata['course']->id);

        if ($acadyears && count($groups->groups) > 0) {
            $groupoverrides = new group_overrides($this->_customdata['course']->id);

            $context = [
                'labeltext' => get_string('coursesetting:groupoverrides', 'timetableevents'),
                'multiple' => true,
                'selectionid' => 'groupoverrides',
                'items' => $groupoverrides->export_for_template($OUTPUT)
            ];

            $mform->addElement(
                'html',
                $OUTPUT->render_from_template('mod_timetableevents/groupoverrides', $context)
            );

            $groupbuttonattributes['class'] = 'btn';
            $mform->addElement('button', 'groupoverrides',
                get_string('coursesetting:groupoverride:add', 'timetableevents'), null, $groupbuttonattributes);
        } else {
            $mform->addElement('static', 'groupoverrides', get_string('coursesetting:groupoverrides', 'timetableevents'),
                get_string('coursesetting:groupoverridesoracadyears', 'timetableevents'));
        }
        // Get course sections.
        $sections = data_manager::get_course_sections($this->_customdata['course']->id);
        $sectionoptions = [];
        foreach ($sections as $section) {
            $sectionoptions[$section->id] = $section->name;
        }

        $mform->addElement('select', 'firstsection', get_string('coursesetting:firstsection', 'timetableevents'), $sectionoptions);
        $mform->setType('firstsection', PARAM_INT);

        $intervaloptions = [
            teaching_intervals::WEEKLY => get_string('coursesetting:teachinginverval:weekly', 'timetableevents'),
            teaching_intervals::FORTNIGHTLY => get_string('coursesetting:teachinginverval:fortnightly', 'timetableevents'),
            teaching_intervals::DAILY => get_string('coursesetting:teachinginverval:daily', 'timetableevents')
        ];
        $mform->addElement('select', 'teachinginverval',
            get_string('coursesetting:teachinginverval', 'timetableevents'), $intervaloptions);

        $options = array(
            'multiple' => true,
            'showsuggestions' => true,
            'placeholder' => get_string('modsetting:placeholder', 'timetableevents'),
        );
        $mform->addElement('autocomplete', 'readingweek',
            get_string('coursesetting:readingweeks', 'timetableevents'), $sectionoptions, $options);
        $mform->hideIf('readingweek', 'teachinginverval', 'neq', teaching_intervals::FORTNIGHTLY);

        $mform->addElement('autocomplete', 'excluded',
            get_string('coursesetting:excludedsections', 'timetableevents'), $sectionoptions, $options);

        $mform->addElement('text', 'footertext',
            get_string('coursesetting:footertext', 'timetableevents'));
        $mform->setType('footertext', PARAM_TEXT);

        $mform->addElement('hidden', 'removeoverrides', null);
        $mform->setType('removeoverrides', PARAM_TEXT);

        $this->add_action_buttons();

    }

    /**
     * Create terms element based on acad year value.
     */
    public function definition_after_data() {
        $mform = $this->_form;
        $acadyearel = $mform->getElement('academicyear');
        $year = $acadyearel->getValue();

        $terms = $this->_customdata['terms'];

        if ($year) {
            $terms = $this->_customdata['terms'][$year[0]];
        } else {
            $terms = array_values($terms);
        }

        if ($terms) {
            $termoptions = [];
            foreach ($terms as $term) {
                $termoptions[$term->termid] = get_string('coursesetting:term:options', 'timetableevents',
                    [
                        'termno' => $term->termno,
                        'startdate' => $term->startdateformatted,
                        'enddate' => $term->enddateformatted
                    ]
                );
            }

            $termel = $mform->createElement('select', 'term', get_string('coursesetting:term', 'timetableevents'), $termoptions);
            $mform->insertElementBefore($termel, 'teachingstartdate');
            $mform->setDefault('teachingstartdate', $term->startdate);

        } else {
            $termel = $mform->createElement('static', 'term', get_string('coursesetting:term', 'timetableevents'),
                get_string('coursesetting:academicyear:notconfigured', 'timetableevents'));
            $mform->insertElementBefore($termel, 'teachingstartdate');
        }
    }
}
