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
 * JS for forms.
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Sarah Cotton <sarah.cotton@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/str', 'jquery'], function(ajax, str, $) {

    return {
        /**
         * Initialize JS for forms.
         * @access public
         * @param {Object} params
         */
        course: function(course, sesskey) {
            const terms = JSON.parse(document.querySelector('[name="termsjson"]').value);
            const acadyear = document.getElementById('id_academicyear');
            const term = document.getElementById('id_term');
            const group = document.getElementById('id_groupoverrides');
            const group_selections = document.getElementById('groupoverrides_selections');

            var event = new Event('change', { bubbles: true });

            acadyear.addEventListener('change',function(){
                const yearid = document.getElementById("id_academicyear").value;
                const year = terms[yearid];
                let list = [];

                // Construct new options list for selected year.
                for (const key in year) {
                    list[year[key].termid] = year[key].termname + ' ' + year[key].startdateformatted
                            + ' - ' + year[key].enddateformatted;
                }

                // Remove current options from select.
                const id_term = document.getElementById("id_term");
                id_term.options.length = 0;

                // Replace select options.
                for (const key in list) {
                    id_term.options[id_term.options.length] = new Option(list[key], key);
                }

                // Trigger update of term start date.
                if (!localStorage.getItem('tte_academicyear')) {
                    term.dispatchEvent(event);
                }

            });

            term.addEventListener('change',function(){
                const year = document.getElementById('id_academicyear').value;
                const startingtermid = document.getElementById('id_term').value;

                document.getElementById('id_teachingstartdate_day').value = parseInt(terms[year][startingtermid]['day'], 10);
                document.getElementById('id_teachingstartdate_month').value = parseInt(terms[year][startingtermid]['month'], 10);
                document.getElementById('id_teachingstartdate_year').value = terms[year][startingtermid]['year'];

            });

            if (group_selections) {
                group_selections.addEventListener('click',function(e){
                    // Get value of clicked element and add to hidden element
                    // to pass back to the form.
                    let removegroup = e.target.getAttribute('data-value');
                    let removegroupelement = e.target;
                    let removeoverrides = document.querySelector('[name="removeoverrides"]');
                    removeoverrides.value = removeoverrides.value + ',' + removegroup;

                    // Hide element.
                    removegroupelement.style.display = "none";
                });
            }

            if (group) {
                group.addEventListener('click',function(){

                    // Save currently set data to Web Storage API.
                    localStorage.setItem('tte_academicyear', document.getElementById('id_academicyear').value);
                    localStorage.setItem('tte_term', document.getElementById('id_term').value);
                    localStorage.setItem('tte_teachingstartdate_day', document.getElementById('id_teachingstartdate_day').value);
                    localStorage.setItem('tte_teachingstartdate_month',
                        document.getElementById('id_teachingstartdate_month').value);
                    localStorage.setItem('tte_teachingstartdate_year', document.getElementById('id_teachingstartdate_year').value);
                    localStorage.setItem('tte_firstsection', document.getElementById('id_firstsection').value);
                    localStorage.setItem('tte_teachinginverval', document.getElementById('id_teachinginverval').value);

                    const readingweek = document.getElementById('id_readingweek');
                    localStorage.setItem('tte_readingweek',
                        Array.from(readingweek.querySelectorAll("option:checked"),v => v.value));

                    const excluded = document.getElementById('id_excluded');
                    localStorage.setItem('tte_excluded', Array.from(excluded.querySelectorAll("option:checked"),v => v.value));
                    localStorage.setItem('tte_footertext', document.getElementById('id_footertext').value);

                    // Redirect user to group overrides settings.
                    let year = document.getElementById('id_academicyear').value;
                    location.href = '/mod/timetableevents/group.php?id=' + course + '&year=' + year + '&sesskey=' + sesskey;
                });
            }

            // Check if Web Storage data has been set and load if present.
            if (localStorage.getItem('tte_academicyear')) {

                document.getElementById('id_academicyear').value = localStorage.getItem('tte_academicyear');
                // Trigger the acad year event so we have data for the correct year in the term dropdown.
                acadyear.dispatchEvent(event);
                document.getElementById('id_term').value = localStorage.getItem('tte_term');
                document.getElementById('id_teachingstartdate_day').value = localStorage.getItem('tte_teachingstartdate_day');
                document.getElementById('id_teachingstartdate_month').value = localStorage.getItem('tte_teachingstartdate_month');
                document.getElementById('id_teachingstartdate_year').value = localStorage.getItem('tte_teachingstartdate_year');
                document.getElementById('id_firstsection').value = localStorage.getItem('tte_firstsection');
                document.getElementById('id_teachinginverval').value = localStorage.getItem('tte_teachinginverval');

                const readingweek = document.getElementById('id_readingweek');
                for (let i = 0; i < readingweek.options.length; i++) {
                    readingweek.options[i].selected =
                        localStorage.getItem('tte_readingweek').indexOf(readingweek.options[i].value) >= 0;
                }

                var excluded = document.getElementById('id_excluded');
                for (let i = 0; i < excluded.options.length; i++) {
                    excluded.options[i].selected = localStorage.getItem('tte_excluded').indexOf(excluded.options[i].value) >= 0;
                }
                document.getElementById('id_footertext').value = localStorage.getItem('tte_footertext');

                remove_storage_keys();

            }
        },

        group: function() {
            const terms = JSON.parse(document.querySelector('[name="termsjson"]').value);
            const term = document.getElementById('id_startingtermid');
            let hiddenyear = document.querySelector('[name="year"]').value;

            term.addEventListener('change',function(){
                let startingtermid = document.getElementById('id_startingtermid').value;

                document.getElementById('id_teachingstartdate_day').value = parseInt(terms[hiddenyear][startingtermid]['day'], 10);
                document.getElementById('id_teachingstartdate_month').value =
                    parseInt(terms[hiddenyear][startingtermid]['month'], 10);
                document.getElementById('id_teachingstartdate_year').value = terms[hiddenyear][startingtermid]['year'];

            });

            window.addEventListener('beforeunload', function() {
                let url = document.activeElement.href;
                // If we're moving away from the course settings page without saving, remove the storage keys.
                if (!url.includes('/mod/timetableevents/course.php')) {
                    remove_storage_keys();
                }
            });
        },

        instance: function() {

            const courseoverride = document.getElementById('id_courseoverride');
            const nogroups = str.get_string('modsetting:nogroups', 'timetableevents');

            courseoverride.addEventListener('change',function(){

                let courseid = document.getElementById('id_courseoverride').value;

                let request = {
                    methodname: 'mod_timetableevents_select_groups',
                    args: {
                        courseid: courseid
                    }
                };

                let promise = ajax.call([request])[0];

                promise.then(
                    function(value) {
                        // Remove current options from select.
                        const id_groupid = document.getElementById("id_groupid");
                        id_groupid.options.length = 0;

                        // Replace select options.
                        if (value.length > 0) {
                            for (const key in value) {
                                id_groupid.options[id_groupid.options.length] = new Option(value[key].name, value[key].id);
                            }
                            id_groupid.disabled = false;
                        } else {
                            $.when(nogroups).done(function(localizednogroups) {
                                id_groupid.options[id_groupid.options.length] =
                                    new Option(localizednogroups, 0);
                            });

                            id_groupid.disabled = true;
                        }
                    }
                );
            });
        }
    };
});

function remove_storage_keys() {
    localStorage.removeItem('tte_academicyear');
    localStorage.removeItem('tte_term');
    localStorage.removeItem('tte_teachingstartdate_day');
    localStorage.removeItem('tte_teachingstartdate_month');
    localStorage.removeItem('tte_teachingstartdate_year');
    localStorage.removeItem('tte_firstsection');
    localStorage.removeItem('tte_teachinginverval');
    localStorage.removeItem('tte_readingweek');
    localStorage.removeItem('tte_excluded');
    localStorage.removeItem('tte_footertext');
}
