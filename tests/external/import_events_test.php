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
 * Tests for import_events external function
 *
 * @package   mod_timetableevents
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_timetableevents\external;

use core_external\external_api;

/**
 * Tests for events import API
 * @covers \mod_timetableevents\external\import_events
 */
class import_events_test extends \advanced_testcase {
    /**
     * @var array Generated entities.
     */
    private $generated = [];

    /**
     * Generate a course, group, and timetableevents event.
     *
     * @return void
     * @throws \coding_exception
     */
    public function setUp(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        // Workaround for external_api::call_external_function requiring sesskey.
        $_POST['sesskey'] = sesskey();

        // Set up initial data.
        $generator = $this->getDataGenerator();
        $this->generated['course'] = $generator->create_course(['shortname' => 'course123']);
        $this->generated['group'] = $generator->create_group(
            ['courseid' => $this->generated['course']->id, 'idnumber' => 'group123']
        );
        $this->generated['event'] = $generator->create_event([
            'name' => 'Test event 1',
            'courseid' => $this->generated['course']->id,
            'groupid' => $this->generated['group']->id,
            'eventtype' => 'group',
            'component' => import_events::EVENT_COMPONENT,
            'timestart' => strtotime('1 hour'),
            'location' => '123 Fake Street',
            'timeduration' => HOURSECS,
            'uuid' => 'f02089cd-4f35-488d-8656-0014ea79c801',
        ]);
        $tz = new \DateTimeZone('UTC');
        $this->generated['timestart'] = (new \DateTime('1 Day', $tz))->format(import_events::DATE_FORMAT);
        $this->generated['timeend'] = (new \DateTime('1 Day 2 hours', $tz))->format(import_events::DATE_FORMAT);
    }

    /**
     * Calling the function without any events to import returns an empty result set.
     *
     * @return void
     */
    public function test_no_events(): void {
        $response = external_api::call_external_function('mod_timetableevents_import_events', ['events' => []]);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertEmpty($response['data']['warnings']);
    }

    /**
     * Importing an event with a course shortname that doesn't correspond to a course returns a warning.
     *
     * @return void
     */
    public function test_invalid_courseshortname(): void {
        $args = [
            'events' => [
                [
                    'idnumber' => '03e7f866-339c-49e3-996f-2eda0f096057',
                    'courseshortname' => 'course456',
                    'name' => 'Test event 2',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => $this->generated['timeend'],
                    'location' => '123 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertCount(1, $response['data']['warnings']);
        $this->assertEquals([
            'item' => $args['events'][0]['courseshortname'],
            'itemid' => 0,
            'warningcode' => 'invalidcourseshortname',
            'message' => get_string('invalidcourseshortname', 'mod_timetableevents'),
        ], $response['data']['warnings'][0]);
    }

    /**
     * Importing an event with a groupidnumber that doesn't correspond to a group returns a warning.
     *
     * @return void
     */
    public function test_invalid_groupidnumber(): void {
        $args = [
            'events' => [
                [
                    'idnumber' => '03e7f866-339c-49e3-996f-2eda0f096057',
                    'courseshortname' => 'course123',
                    'groupidnumber' => 'group456',
                    'name' => 'Test event 2',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => $this->generated['timeend'],
                    'location' => '123 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertCount(1, $response['data']['warnings']);
        $this->assertEquals([
            'item' => $args['events'][0]['groupidnumber'],
            'itemid' => 0,
            'warningcode' => 'invalidgroupidnumber',
            'message' => get_string('invalidgroupidnumber', 'mod_timetableevents'),
        ], $response['data']['warnings'][0]);
    }

    /**
     * Importing an event with timestart in the wrong format returns a warning.
     *
     * @return void
     */
    public function test_invalid_timestart(): void {
        $args = [
            'events' => [
                [
                    'idnumber' => '03e7f866-339c-49e3-996f-2eda0f096057',
                    'courseshortname' => 'course123',
                    'groupidnumber' => 'group123',
                    'name' => 'Test event 2',
                    'timestart' => '12pm 5/6/23',
                    'timeend' => $this->generated['timeend'],
                    'location' => '123 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertCount(1, $response['data']['warnings']);
        $messages = 'Unexpected data found.; Unexpected data found.; Not enough data available to satisfy format';
        $this->assertEquals([
            'item' => $args['events'][0]['timestart'],
            'itemid' => 0,
            'warningcode' => 'invalidtime',
            'message' => get_string('invalidtime', 'mod_timetableevents', $messages),
        ], $response['data']['warnings'][0]);
    }

    /**
     * Importing an event with timeend in the wrong format returns a warning.
     *
     * @return void
     */
    public function test_invalid_timeend(): void {
        $args = [
            'events' => [
                [
                    'idnumber' => '03e7f866-339c-49e3-996f-2eda0f096057',
                    'courseshortname' => 'course123',
                    'groupidnumber' => 'group123',
                    'name' => 'Test event 2',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => '1pm 5/6/23',
                    'location' => '123 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertCount(1, $response['data']['warnings']);
        $messages = 'Unexpected data found.; Unexpected data found.; Not enough data available to satisfy format';
        $this->assertEquals([
            'item' => $args['events'][0]['timeend'],
            'itemid' => 0,
            'warningcode' => 'invalidtime',
            'message' => get_string('invalidtime', 'mod_timetableevents', $messages),
        ], $response['data']['warnings'][0]);
    }

    /**
     * Importing an event with timeend before timestart returns a warning.
     *
     * @return void
     */
    public function test_timeend_before_timestart(): void {
        $args = [
            'events' => [
                [
                    'idnumber' => '03e7f866-339c-49e3-996f-2eda0f096057',
                    'courseshortname' => 'course123',
                    'groupidnumber' => 'group123',
                    'name' => 'Test event 2',
                    'timestart' => $this->generated['timeend'],
                    'timeend' => $this->generated['timestart'],
                    'location' => '123 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertCount(1, $response['data']['warnings']);
        $times = json_encode((object)['timestart' => $args['events'][0]['timestart'], 'timeend' => $args['events'][0]['timeend']]);
        $this->assertEquals([
                'item' => $times,
                'itemid' => 0,
                'warningcode' => 'invalidtimeend',
                'message' => get_string('invalidtimeend', 'mod_timetableevents'),
        ], $response['data']['warnings'][0]);
    }

    /**
     * Importing an event with valid fields and an idnumber that doesn't exist yet creates a new event.
     *
     * @return void
     */
    public function test_create_new_event(): void {
        global $DB;
        $args = [
            'events' => [
                [
                    'idnumber' => '03e7f866-339c-49e3-996f-2eda0f096057',
                    'courseshortname' => 'course123',
                    'groupidnumber' => 'group123',
                    'name' => 'Test event 2',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => $this->generated['timeend'],
                    'location' => '345 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(1, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertCount(0, $response['data']['warnings']);

        // Ensure the original event has not changed.
        $originalevent = $DB->get_record(
            'event',
            ['id' => $this->generated['event']->id],
            'uuid, name, courseid, groupid, timestart, timeduration, eventtype, component, location',
            MUST_EXIST
        );
        $expectedevent = (object)[
            'uuid' => $this->generated['event']->uuid,
            'name' => $this->generated['event']->name,
            'courseid' => $this->generated['event']->courseid,
            'groupid' => $this->generated['event']->groupid,
            'timestart' => $this->generated['event']->timestart,
            'timeduration' => $this->generated['event']->timeduration,
            'eventtype' => $this->generated['event']->eventtype,
            'component' => $this->generated['event']->component,
            'location' => $this->generated['event']->location,
        ];
        $this->assertEquals($expectedevent, $originalevent);

        // Ensure the new event was created correctly.
        $tz = new \DateTimeZone('UTC');
        $timestart = \DateTime::createFromFormat(import_events::DATE_FORMAT, $this->generated['timestart'], $tz);
        $timeend = \DateTime::createFromFormat(import_events::DATE_FORMAT, $this->generated['timeend'], $tz);
        $timeduration = $timeend->getTimestamp() - $timestart->getTimestamp();
        $newevent = $DB->get_record(
            'event',
            ['uuid' => $args['events'][0]['idnumber']],
            'uuid, name, courseid, groupid, timestart, timeduration, eventtype, component, location',
            MUST_EXIST
        );
        $expectedevent = (object)[
            'uuid' => $args['events'][0]['idnumber'],
            'name' => $args['events'][0]['name'],
            'courseid' => $this->generated['course']->id,
            'groupid' => $this->generated['group']->id,
            'timestart' => $timestart->getTimestamp(),
            'timeduration' => $timeduration,
            'eventtype' => 'group',
            'component' => import_events::EVENT_COMPONENT,
            'location' => $args['events'][0]['location'],
        ];
        $this->assertEquals($expectedevent, $newevent);
    }

    /**
     * Importing an event with valid fields and an idnumber that does exist updates that event.
     *
     * @return void
     */
    public function test_update_existing_event(): void {
        global $DB;
        $newcourse = $this->getDataGenerator()->create_course(['shortname' => 'course456']);
        $newgroup = $this->getDataGenerator()->create_group(['idnumber' => 'group456', 'courseid' => $newcourse->id]);
        $this->assertEquals(1, $DB->count_records('event'));
        $args = [
            'events' => [
                [
                    'idnumber' => $this->generated['event']->uuid,
                    'courseshortname' => $newcourse->shortname,
                    'groupidnumber' => $newgroup->idnumber,
                    'name' => 'Test event 2',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => $this->generated['timeend'],
                    'location' => '345 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(1, $response['data']['updated']);
        $this->assertCount(0, $response['data']['warnings']);

        // Check existing event was updated.
        $tz = new \DateTimeZone('UTC');
        $timestart = \DateTime::createFromFormat(import_events::DATE_FORMAT, $this->generated['timestart'], $tz);
        $timeend = \DateTime::createFromFormat(import_events::DATE_FORMAT, $this->generated['timeend'], $tz);
        $timeduration = $timeend->getTimestamp() - $timestart->getTimestamp();
        $originalevent = $DB->get_record(
            'event',
            ['id' => $this->generated['event']->id],
            'uuid, name, courseid, groupid, timestart, timeduration, eventtype, component, location',
            MUST_EXIST
        );
        $expectedevent = (object)[
            'uuid' => (string)$this->generated['event']->uuid,
            'name' => $args['events'][0]['name'],
            'courseid' => $newcourse->id,
            'groupid' => $newgroup->id,
            'timestart' => (string)$timestart->getTimestamp(),
            'timeduration' => (string)$timeduration,
            'eventtype' => 'group',
            'component' => $this->generated['event']->component,
            'location' => $args['events'][0]['location'],
        ];
        $this->assertEquals($expectedevent, $originalevent);

        // Check no new event was created.
        $this->assertEquals(1, $DB->count_records('event'));
    }

    /**
     * Importing a mixture of new, existing and invalid events produces a report indicating what happened.
     *
     * @return void
     */
    public function test_multiple_events(): void {
        global $DB;
        $generator = $this->getDataGenerator();
        $newevent = $generator->create_event([
            'name' => 'Test event 2',
            'courseid' => $this->generated['course']->id,
            'groupid' => $this->generated['group']->id,
            'eventtype' => 'group',
            'component' => import_events::EVENT_COMPONENT,
            'timestart' => strtotime('2 hours'),
            'location' => '123 Fake Street',
            'timeduration' => HOURSECS,
            'uuid' => '9480fb56-38a4-4501-80d9-52cfce7e6148',
        ]);
        $newcourse = $generator->create_course(['shortname' => 'course456']);
        $newgroup = $generator->create_group(['idnumber' => 'group456', 'courseid' => $newcourse->id]);

        $args = [
            'events' => [
                [ // Update course, group, start and end of existing event.
                    'idnumber' => $newevent->uuid,
                    'courseshortname' => $newcourse->shortname,
                    'groupidnumber' => $newgroup->idnumber,
                    'name' => 'Test event 2',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => $this->generated['timeend'],
                    'location' => '123 Fake Street',
                ],
                [ // Create a new event.
                    'idnumber' => '03e7f866-339c-49e3-996f-2eda0f096057',
                    'courseshortname' => $this->generated['course']->shortname,
                    'groupidnumber' => $this->generated['group']->idnumber,
                    'name' => 'Test event 3',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => $this->generated['timeend'],
                    'location' => '345 Fake Street',
                ],
                [ // Create an event with an invalid course.
                    'idnumber' => '9161c094-1501-4209-bf4b-acd0c4b79bb6',
                    'courseshortname' => 'course789',
                    'groupidnumber' => $this->generated['group']->idnumber,
                    'name' => 'Test event 4',
                    'timestart' => $this->generated['timestart'],
                    'timeend' => $this->generated['timeend'],
                    'location' => '345 Fake Street',
                ],
                [ // Create an event with an invalid start time.
                    'idnumber' => 'd7ce5533-7e4a-47a2-b55f-a08cf9553638',
                    'courseshortname' => $this->generated['course']->shortname,
                    'groupidnumber' => $this->generated['group']->idnumber,
                    'name' => 'Test event 5',
                    'timestart' => '1pm 5/6/23',
                    'timeend' => $this->generated['timeend'],
                    'location' => '345 Fake Street',
                ],
            ],
        ];
        $response = external_api::call_external_function('mod_timetableevents_import_events', $args);
        $this->assertFalse($response['error']);
        $this->assertEquals(1, $response['data']['created']);
        $this->assertEquals(1, $response['data']['updated']);
        $this->assertCount(2, $response['data']['warnings']);

        // Check the original event has not changed.
        $originalevent = $DB->get_record(
            'event',
            ['id' => $this->generated['event']->id],
            'uuid, name, courseid, groupid, timestart, timeduration, eventtype, component, location',
            MUST_EXIST
        );
        $expectedevent = (object)[
                'uuid' => $this->generated['event']->uuid,
                'name' => $this->generated['event']->name,
                'courseid' => $this->generated['event']->courseid,
                'groupid' => $this->generated['event']->groupid,
                'timestart' => $this->generated['event']->timestart,
                'timeduration' => $this->generated['event']->timeduration,
                'eventtype' => $this->generated['event']->eventtype,
                'component' => $this->generated['event']->component,
                'location' => $this->generated['event']->location,
        ];
        $this->assertEquals($expectedevent, $originalevent);

        // Check the second generated event has changed.
        $tz = new \DateTimeZone('UTC');
        $timestart = \DateTime::createFromFormat(import_events::DATE_FORMAT, $this->generated['timestart'], $tz);
        $timeend = \DateTime::createFromFormat(import_events::DATE_FORMAT, $this->generated['timeend'], $tz);
        $timeduration = $timeend->getTimestamp() - $timestart->getTimestamp();
        $modifiedevent = $DB->get_record(
            'event',
            ['id' => $newevent->id],
            'uuid, name, courseid, groupid, timestart, timeduration, eventtype, component, location',
            MUST_EXIST
        );
        $expectedevent = (object)[
            'uuid' => $newevent->uuid,
            'name' => $newevent->name,
            'courseid' => $newcourse->id,
            'groupid' => $newgroup->id,
            'timestart' => $timestart->getTimestamp(),
            'timeduration' => $timeduration,
            'eventtype' => $newevent->eventtype,
            'component' => $newevent->component,
            'location' => $newevent->location,
        ];
        $this->assertEquals($expectedevent, $modifiedevent);

        // Check that a third event was created.
        $newevent = $DB->get_record(
            'event',
            ['uuid' => $args['events'][1]['idnumber']],
            'uuid, name, courseid, groupid, timestart, timeduration, eventtype, component, location',
            MUST_EXIST
        );
        $expectedevent = (object)[
            'uuid' => $args['events'][1]['idnumber'],
            'name' => $args['events'][1]['name'],
            'courseid' => $this->generated['course']->id,
            'groupid' => $this->generated['group']->id,
            'timestart' => $timestart->getTimestamp(),
            'timeduration' => $timeduration,
            'eventtype' => 'group',
            'component' => import_events::EVENT_COMPONENT,
            'location' => $args['events'][1]['location'],
        ];
        $this->assertEquals($expectedevent, $newevent);

        // Check that no additional events were created.
        [$insql, $params] = $DB->get_in_or_equal([$args['events'][2]['idnumber'], $args['events'][3]['idnumber']]);
        $this->assertFalse($DB->record_exists_select('event', 'uuid ' . $insql, $params));
        $this->assertEquals(3, $DB->count_records('event'));

        // Check we got the expected warnings.
        $messages = 'Unexpected data found.; Unexpected data found.; Not enough data available to satisfy format';
        $expectedwarnings = [
            [
                'item' => $args['events'][2]['courseshortname'],
                'itemid' => 2,
                'warningcode' => 'invalidcourseshortname',
                'message' => get_string('invalidcourseshortname', 'mod_timetableevents'),
            ],
            [
                'item' => $args['events'][3]['timestart'],
                'itemid' => 3,
                'warningcode' => 'invalidtime',
                'message' => get_string('invalidtime', 'mod_timetableevents', $messages),
            ],
        ];
        $this->assertEquals($expectedwarnings, $response['data']['warnings']);
    }

    /**
     * Trying to import as a user without the mod/timetablevents:import capability fails.
     *
     * @return void
     */
    public function test_import_without_capability(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user->id);
        $_POST['sesskey'] = sesskey();

        $response = external_api::call_external_function('mod_timetableevents_import_events', ['events' => []]);

        $this->assertTrue($response['error']);
        $this->assertEquals('nopermissions', $response['exception']->errorcode);
        $this->assertTrue(
            str_contains(
                $response['exception']->message,
                get_string('timetableevents:import', 'mod_timetableevents')
            )
        );
    }

    /**
     * Trying to import as a non-admin user with the mod/timetablevents:import capability succeeds.
     *
     * @return void
     */
    public function test_import_with_capability(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $roleid = $generator->create_role();
        $context = \context_system::instance();
        assign_capability('mod/timetableevents:import', CAP_ALLOW, $roleid, $context);
        $generator->role_assign($roleid, $user->id, $context);
        $this->setUser($user->id);
        $_POST['sesskey'] = sesskey();

        $response = external_api::call_external_function('mod_timetableevents_import_events', ['events' => []]);
        $this->assertFalse($response['error']);
        $this->assertEquals(0, $response['data']['created']);
        $this->assertEquals(0, $response['data']['updated']);
        $this->assertEmpty($response['data']['warnings']);
    }
}
