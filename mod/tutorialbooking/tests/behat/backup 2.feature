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
        And I log in as "teacher"
        And I am on "Course 1" course homepage
        And I add a new timeslot to "Happy days" tutorial booking with:
            | Title | Slot 1 |
            | Max Number of Students | 1 |
            | Position | Top of the Page |
        And I add a new timeslot to "Happy days" tutorial booking with:
            | Title | Slot 2 |
            | Max Number of Students | 2 |
            | Position | Bottom of the Page |
        And I log out
        And I log in as "student"
        And I am on "Course 1" course homepage
        And I sign up to "Slot 2" in "Happy days" tutorial booking
        And I log out

    Scenario: Duplicate a tutorial booking activity (Covers T34 MOODLETEST-1370)
        Given I log in as "teacher"
        And I am on "Course 1" course homepage with editing mode on
        When I duplicate "Happy days" activity
        Then I should see "Happy days (copy)"
        And I should see "Slot 1" in position "1" of "Happy days (copy)" tutorial booking
        And I should see "Slot 2" in position "2" of "Happy days (copy)" tutorial booking
        And there should be "1" free space of "1" total spaces available on "Slot 1" of "Happy days (copy)" tutorial booking
        And there should be "2" free space of "2" total spaces available on "Slot 2" of "Happy days (copy)" tutorial booking
