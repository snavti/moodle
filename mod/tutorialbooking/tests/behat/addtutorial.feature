@mod @mod_tutorialbooking @uon
Feature: Add a signup sheet activity to a course and add some sessions
    In order to allow users to signup to sessions
    As a teacher
    I need to be able to add signup sheet activities to a course.

    Scenario: Add a signup sheet activity and create two sessions.
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | student1 | Student   | 1        | student1@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | student1 | C1     | student        |
        And I log in as "teacher1"
        And I am on "Course 1" course homepage with editing mode on
        And I add a "Signup sheet" to section "1" and I fill the form with:
            | Signup sheet title | Tutorial booking |
            | Signup sheet notes | This is a tutorial booking form |
            | Locked | No |
            | Privacy | Users can see all signups |
            | Availability | Show on course page |
        And I follow "Tutorial booking"
        And I add a new session to signup sheet with:
            | Title | Slot 1 |
            | Number of places | 10 |
            | Position | Top of the page |
        And I add a new session to signup sheet with:
            | Title | Slot 2 |
            | Number of places | 5 |
            | Position | Bottom of the page |
        And I log out
        When I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        Then I should see "Slot 1"
        And I should see "Slot 2"
        And I should see "Sign me up for this session"
