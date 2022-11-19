@mod @mod_tutorialbooking @uon
Feature: A signup sheet can be locked to prevent users from modifying signups
    In order to finalise signups
    As a teacher
    I should be able to lock a signup sheet

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | student1 | Student   | 1        | student1@example.com |
            | student2 | Student   | 2        | student2@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | student1 | C1     | student        |
            | student2 | C1     | student        |
        And the following "activities" exist:
            | activity        | course | idnumber | name             | intro                           | locked |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking | 1      |
        And I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And I add a new session to signup sheet with:
            | Title | Slot 1 |
            | Number of places | 1 |
            | Position | Top of the page |
        And I add a new session to signup sheet with:
            | Title | Slot 2 |
            | Number of places | 2 |
            | Position | Bottom of the page |
        And I log out

    Scenario: A student cannot sign up to a session while a signup sheet is locked.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        Then I should not be able to sign up to signup sheet

    @app @javascript
    Scenario: A student cannot sign up to a session while a signup sheet is locked in the app
        Given I enter the app
        And I log in as "student1"
        And I press "Course 1" near "Course overview" in the app
        When I press "Tutorial booking" in the app
        Then I should not see "Sign me up for this session"

    Scenario: A student cannot remove themselves from a session while a signup sheet is locked.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And in "Slot 1" of signup sheet I add:
            | student1 |
        And I log out
        When I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        Then I should see I am signed up to "Slot 1" in signup sheet
        But I should not be able to remove my sign up from signup sheet

    @app @javascript
    Scenario: A student cannot remove themselves from a session while a signup sheet is locked in the app
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And in "Slot 1" of signup sheet I add:
            | student1 |
        And I log out
        When I enter the app
        And I log in as "student1"
        And I press "Course 1" near "Course overview" in the app
        And I press "Tutorial booking" in the app
        Then I should see "You are signed up to this session"
        But I should not see "Remove my signu"
