@catalyst @javascript @mod_timetableevents
Feature: Import timetable events

  Background:
    Given the following "courses" exist:
      | shortname | fullname | startdate           |
      | C1        | Course 1 | ## yesterday ##%s## |
      | C2        | Course 2 | ## yesterday ##%s## |
    And the following "groups" exist:
      | idnumber | course | name    |
      | G1       | C1     | Group 1 |
      | G2       | C1     | Group 2 |
    And the following "users" exist:
      | username    | timezone |
      | group1user  | UTC      |
      | group2user  | UTC      |
      | course2user | UTC      |
      | teacher1    | UTC      |
    And the following "course enrolments" exist:
      | user        | course  | enrol  | role           |
      | group1user  | C1      | manual | student        |
      | group2user  | C1      | manual | student        |
      | course2user | C2      | manual | student        |
      | teacher1    | C1      | manual | editingteacher |
    And the following "group members" exist:
      | user       | group |
      | group1user | G1    |
      | group2user | G2    |
      | teacher1   | G1    |
      | teacher1   | G2    |
    And the following "blocks" exist:
      | blockname         | contextlevel | reference | pagetypepattern | defaultregion |
      | calendar_month    | System       | C1        | course-view-*   | side-pre      |
    And I send the timetableevents import web service the following:
      | idnumber                             | name    | timestart                     | timeend                          | courseshortname | groupidnumber | location    |
      | a34a483b-7305-4826-afcf-1bac473ba265 | Event 1 | ## 12pm ##%Y-%m-%dT%H:%M:%S## | ## 1 pm ##%Y-%m-%dT%H:%M:%S##    | C1              | G1            | 123 Fake St |
      | 971717c5-b136-4525-bcbd-9c6488a01bc9 | Event 2 | ## 1 pm ##%Y-%m-%dT%H:%M:%S## | ## 2 pm ##%Y-%m-%dT%H:%M:%S##    | C1              | G2            | 123 Fake St |
      | 23eb0e25-2630-457c-8406-336d26e477aa | Event 3 | ## 12pm ##%Y-%m-%dT%H:%M:%S## | ## 12:30 pm##%Y-%m-%dT%H:%M:%S## | C1              |               | 123 Fake St |
      | 1f552e36-4d08-4337-8c4e-48c894ff09f5 | Event 4 | ## 12pm ##%Y-%m-%dT%H:%M:%S## | ## 12:30 pm##%Y-%m-%dT%H:%M:%S## | C2              |               | 123 Fake St |

  Scenario: View group 1 events
    Given I am on the "Course 1" course page logged in as group1user
    When I follow "Full calendar"
    And I press "Month"
    And I click on "Day" "link"
    Then I should see "Event 1"
    And I should see "Event 3"
    And I should not see "Event 2"
    And I should not see "Event 4"
    And I should see "Today, 12:00 PM » 1:00 PM" in the "div[data-event-title='Event 1'] .description" "css_element"
    And I should see "123 Fake St" in the "div[data-event-title='Event 1'] .description" "css_element"
    And I should see "Course 1" in the "div[data-event-title='Event 1'] .description" "css_element"
    And I should see "Group 1" in the "div[data-event-title='Event 1'] .description" "css_element"
    And I should see "Today, 12:00 PM » 12:30 PM" in the "div[data-event-title='Event 3'] .description" "css_element"
    And I should see "123 Fake St" in the "div[data-event-title='Event 3'] .description" "css_element"
    And I should see "Course 1" in the "div[data-event-title='Event 3'] .description" "css_element"
    And I should not see "Group 1" in the "div[data-event-title='Event 3'] .description" "css_element"

  Scenario: View group 2 events
    Given I am on the "Course 1" course page logged in as group2user
    When I follow "Full calendar"
    And I press "Month"
    And I click on "Day" "link"
    Then I should see "Event 2"
    And I should see "Event 3"
    And I should not see "Event 1"
    And I should not see "Event 4"
    And I should see "Today, 1:00 PM » 2:00 PM" in the "div[data-event-title='Event 2'] .description" "css_element"
    And I should see "123 Fake St" in the "div[data-event-title='Event 2'] .description" "css_element"
    And I should see "Course 1" in the "div[data-event-title='Event 2'] .description" "css_element"
    And I should see "Group 2" in the "div[data-event-title='Event 2'] .description" "css_element"
    And I should see "Today, 12:00 PM » 12:30 PM" in the "div[data-event-title='Event 3'] .description" "css_element"
    And I should see "123 Fake St" in the "div[data-event-title='Event 3'] .description" "css_element"
    And I should see "Course 1" in the "div[data-event-title='Event 3'] .description" "css_element"
    And I should not see "Group 2" in the "div[data-event-title='Event 3'] .description" "css_element"

  Scenario: View course 2 events
    Given I am on the "Course 2" course page logged in as course2user
    When I follow "Full calendar"
    And I press "Month"
    And I click on "Day" "link"
    Then I should see "Event 4"
    And I should not see "Event 1"
    And I should not see "Event 2"
    And I should not see "Event 3"
    And I should see "Today, 12:00 PM » 12:30 PM" in the "div[data-event-title='Event 4'] .description" "css_element"
    And I should see "123 Fake St" in the "div[data-event-title='Event 4'] .description" "css_element"
    And I should see "Course 2" in the "div[data-event-title='Event 4'] .description" "css_element"
    And I should not see "Group" in the "div[data-event-title='Event 4'] .description" "css_element"

  Scenario: View as teacher
    Given I am on the "Course 1" course page logged in as teacher1
    When I follow "Full calendar"
    And I press "Month"
    And I click on "Day" "link"
    Then I should see "Event 1"
    And I should see "Event 2"
    And I should see "Event 3"
    And I should not see "Event 4"
