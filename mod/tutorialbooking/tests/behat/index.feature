@mod @mod_tutorialbooking @uon
Feature: List all Tutorial booking activities in a course
    In order to easily find all Tutorial booking on a course
    As a user
    I need to be able to view the index page.

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email |
            | teacher | Teacher | User | teacher@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1 | 0 |
            | Course 2 | C2 | 0 |
        And the following "course enrolments" exist:
            | user | course | role |
            | teacher | C1 | editingteacher |
        And the following "activities" exist:
            | activity | course | idnumber | name | intro | section |
            | tutorialbooking | C1 | tuorial1 | Happy days | Book your happy day here | 1 |
            | tutorialbooking | C1 | tuorial2 | Fun times | Pick a time for fun | 2 |
            | tutorialbooking | C2 | tuorial3 | Merry munchkins | Be merry with munchkins | 1 |

    Scenario: View the Tutorial booking index page (Covers T28 MOODLETEST-35)
        Given I log in as "teacher"
        And I am on "Course 1" course homepage
        And I follow "Happy days"
        When I click on "Tutorial booking index" "link_or_button"
        Then I should see "Happy days"
        And I should see "Fun times"
        But I should not see "Merry munchkins"
