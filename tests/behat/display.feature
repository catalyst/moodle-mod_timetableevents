@mod_timetableevents
Feature: Display timetable events on the course page

  Background:
    Given the following "mod_timetableevents > academic years" exist:
      | name    |
      | 2022-23 |
    And the following "mod_timetableevents > academic terms" exist:
      | name    | startdate  | enddate    |
      | 2022-23 | 2022-09-12 | 2022-12-31 |
    And the current timetableevents academic year is set to "2022-23"
    And the following config values are set as admin:
      | firstteachingsection | 5          | mod_timetableevents |
      | teachinginterval     | 1          | mod_timetableevents |
      | teachingstartdate    | 1662940800 | mod_timetableevents |
    And the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following "activities" exist:
      | activity        | name | course | idnumber | section | groupmode |
      | timetableevents | 1    | C1     | 1        | 5       | 2         |
      | timetableevents | 2    | C1     | 2        | 6       | 2         |
      | timetableevents | 3    | C1     | 3        | 7       | 2         |
      | timetableevents | 4    | C1     | 4        | 8       | 0         |
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
      | teacher1    | C1      | manual | editingteacher |
    And the following "groups" exist:
      | idnumber | course | name    |
      | G1       | C1     | Group 1 |
      | G2       | C1     | Group 2 |
    And the following "group members" exist:
      | user       | group |
      | group1user | G1    |
      | group2user | G2    |
      | teacher1   | G1    |
      | teacher1   | G2    |
    And the following "mod_timetableevents > events" exist:
      | name           | group  | eventtype | timestart  | timeduration | uuid                                 | location        | course |
      | 12/09/22 17:00 | G1     | group     | 1662973200 | 3600         | f02089cd-4f35-488d-8656-0014ea79c801 | 345 Fake Street | C1     |
      | 12/09/22 19:00 | G2     | group     | 1662980400 | 3600         | f02089cd-4f35-488d-8656-0014ea79c802 | 345 Fake Street | C1     |
      | 13/09/22 16:00 | G1     | group     | 1663056000 | 3600         | f02089cd-4f35-488d-8656-0014ea79c803 | 345 Fake Street | C1     |
      | 13/09/22 17:00 | G2     | group     | 1663059600 | 3600         | f02089cd-4f35-488d-8656-0014ea79c804 | 345 Fake Street | C1     |
      | 14/09/22 17:00 | G1     | group     | 1663146000 | 7200         | 03e7f866-339c-49e3-996f-2eda0f096051 | 345 Fake Street | C1     |
      | 14/09/22 18:00 | G2     | group     | 1663149600 | 7200         | 03e7f866-339c-49e3-996f-2eda0f096052 | 345 Fake Street | C1     |
      | 15/09/22 16:00 | G1     | group     | 1663228800 | 7200         | 03e7f866-339c-49e3-996f-2eda0f096053 | 345 Fake Street | C1     |
      | 15/09/22 17:00 | G2     | group     | 1663232400 | 7200         | 03e7f866-339c-49e3-996f-2eda0f096054 | 345 Fake Street | C1     |
      | 16/09/22 17:00 | G2     | group     | 1663318800 | 7200         | 03e7f866-339c-49e3-996f-2eda0f096055 | 345 Fake Street | C1     |
      | 20/09/22 17:00 | G1     | group     | 1663664400 | 7200         | 03e7f866-339c-49e3-996f-2eda0f096056 | 345 Fake Street | C1     |
      | 22/09/22 17:00 | G2     | group     | 1663837200 | 3600         | f02089cd-4f35-488d-8656-0014ea79c805 | 345 Fake Street | C1     |
      | 28/09/22 17:00 | G1     | group     | 1664355600 | 3600         | f02089cd-4f35-488d-8656-0014ea79c806 | 345 Fake Street | C1     |
      | 01/10/22 18:00 | G2     | group     | 1664618400 | 3600         | f02089cd-4f35-488d-8656-0014ea79c807 | 345 Fake Street | C1     |
      | 05/10/22 17:00 | G1     | group     | 1664960400 | 3600         | f02089cd-4f35-488d-8656-0014ea79c808 | 345 Fake Street | C1     |
      | 07/10/22 18:00 | G2     | group     | 1665136800 | 3600         | f02089cd-4f35-488d-8656-0014ea79c809 | 345 Fake Street | C1     |
      | 12/10/22 17:00 | G1     | group     | 1665565200 | 3600         | f02089cd-4f35-488d-8656-0014ea79c810 | 345 Fake Street | C1     |
      | 15/10/22 17:00 | G2     | group     | 1665824400 | 3600         | f02089cd-4f35-488d-8656-0014ea79c811 | 345 Fake Street | C1     |
    And the following "mod_timetableevents > events" exist:
      | name           | eventtype | timestart  | timeduration | uuid                                 | location                | course |
      | 24/10/22 17:00 | course    | 1666630800 | 3600         | f02089cd-4f35-488d-8656-0014ea79c812 | https://www.google.com/ | C1     |

  Scenario: User in group 1 sees group 1 events and course events
    When I am on the "Course 1" course page logged in as "group1user"
    Then I should see "12/09/22 17:00" in the "Topic 5" "section"
    And I should see "13/09/22 16:00" in the "Topic 5" "section"
    And I should see "14/09/22 17:00" in the "Topic 5" "section"
    And I should see "15/09/22 16:00" in the "Topic 5" "section"
    And I should see "20/09/22 17:00" in the "Topic 5" "section"
    And I should see "28/09/22 17:00" in the "Topic 6" "section"
    And I should see "05/10/22 17:00" in the "Topic 6" "section"
    And I should see "12/10/22 17:00" in the "Topic 7" "section"
    And I should see "24/10/22 17:00" in the "Topic 8" "section"
    And I should not see "12/09/22 19:00" in the "Topic 5" "section"
    And I should not see "13/09/22 17:00" in the "Topic 5" "section"
    And I should not see "14/09/22 18:00" in the "Topic 5" "section"
    And I should not see "15/09/22 17:00" in the "Topic 5" "section"
    And I should not see "16/09/22 17:00" in the "Topic 5" "section"
    And I should not see "01/10/22 18:00" in the "Topic 6" "section"
    And I should not see "07/10/22 18:00" in the "Topic 6" "section"
    And I should not see "15/10/22 17:00" in the "Topic 7" "section"

  Scenario: User in group 2 sees group 2 events and course events
    When I am on the "Course 1" course page logged in as "group2user"
    Then I should see "12/09/22 19:00" in the "Topic 5" "section"
    And I should see "13/09/22 17:00" in the "Topic 5" "section"
    And I should see "14/09/22 18:00" in the "Topic 5" "section"
    And I should see "15/09/22 17:00" in the "Topic 5" "section"
    And I should see "16/09/22 17:00" in the "Topic 5" "section"
    And I should see "01/10/22 18:00" in the "Topic 6" "section"
    And I should see "07/10/22 18:00" in the "Topic 6" "section"
    And I should see "15/10/22 17:00" in the "Topic 7" "section"
    And I should see "24/10/22 17:00" in the "Topic 8" "section"
    And I should not see "12/09/22 17:00" in the "Topic 5" "section"
    And I should not see "13/09/22 16:00" in the "Topic 5" "section"
    And I should not see "14/09/22 17:00" in the "Topic 5" "section"
    And I should not see "15/09/22 16:00" in the "Topic 5" "section"
    And I should not see "20/09/22 17:00" in the "Topic 5" "section"
    And I should not see "28/09/22 17:00" in the "Topic 6" "section"
    And I should not see "05/10/22 17:00" in the "Topic 6" "section"
    And I should not see "12/10/22 17:00" in the "Topic 7" "section"

  @javascript
  Scenario: Teacher can switch between events for each group, and see course events
    Given I am on the "Course 1" course page logged in as "teacher1"
    And I should see "12/09/22 17:00" in the "Topic 5" "section"
    And I should not see "12/09/22 19:00" in the "Topic 5" "section"
    And I should see "Showing events for: Course 1 - Group Group 1 - 2022/09/12, 00:00 - 2022/09/25, 23:59" in the "Topic 5" "section"
    And I should see "24/10/22 17:00" in the "Topic 8" "section"
    When I set the field "View events for group" to "Group 2"
    Then I should not see "12/09/22 17:00" in the "Topic 5" "section"
    And I should see "12/09/22 19:00" in the "Topic 5" "section"
    And I should see "Showing events for: Course 1 - Group Group 2 - 2022/09/12, 00:00 - 2022/09/25, 23:59" in the "Topic 5" "section"
    And I should see "24/10/22 17:00" in the "Topic 8" "section"
    And I should see "Showing events for: Course 1 - 2022/10/24, 00:00 - 2022/11/06, 23:59" in the "Topic 8" "section"
