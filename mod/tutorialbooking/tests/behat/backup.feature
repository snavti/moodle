@mod @mod_tutorialbooking @uon
Feature: Backup and restore
    In order to create a new similar activity
    As a teacher
    I should be able to duplicate the activity.

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email |
            | teacher | Teacher | User | teacher@example.com |
            | student | Student | User | student@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1 | 0 |
            | Course 2 | C2 | 0 |
        And the following "course enrolments" exist:
            | user | course | role |
            | teacher | C1 | editingteacher |
            | student | C1 | student |
        And the following "activities" exist:
            | activity | course | idnumber | name | intro | section |
            | tutorialbooking | C1 | tuorial1 | Happy days | Book your happy day here | 1 |
        And I am on the "Happy days" "mod_tutorialbooking > Management" page logged in as "teacher"
        And I add a new session to signup sheet with:
            | Title | Slot 1 |
            | Number of places | 1 |
            | Position | Top of the page |
        And I add a new session to signup sheet with:
            | Title | Slot 2 |
            | Number of places | 2 |
            | Position | Bottom of the page |
        And I log out
        And I am on the "Happy days" "mod_tutorialbooking > Sessions" page logged in as "student"
        And I sign up to "Slot 2" in signup sheet
        And I log out

    Scenario: Duplicate a signup sheet activity (Covers T34 MOODLETEST-1370)
        Given I log in as "teacher"
        And I am on "Course 1" course homepage with editing mode on
        When I duplicate "Happy days" activity
        And I follow "Happy days (copy)"
        Then I should see "Slot 1" in position "1" of signup sheet
        And I should see "Slot 2" in position "2" of signup sheet
        And there should be "1" free places of "1" total places available on "Slot 1" of signup sheet
        And there should be "2" free places of "2" total places available on "Slot 2" of signup sheet
