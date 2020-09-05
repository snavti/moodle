@mod @mod_tutorialbooking @uon
Feature: Students can signup and remove themselves from signup sheet sessions.
    In order to book onto signup sheets
    As a student
    I need to be able to signup to, and remove myself from, sessions.

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
            | activity        | course | idnumber | name             | intro                           |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking |
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

    Scenario: A student can sign up to a session and then remove themselves from it.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        When I sign up to "Slot 2" in signup sheet
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 2" in signup sheet
        When I remove my sign up from signup sheet
        Then I should be able to sign up to signup sheet

    @app @javascript
    Scenario: A student can sign up to a session and then remove themselves from it in the app
        Given I enter the app
        And I log in as "student1"
        And I press "Course 1" near "Course overview" in the app
        And I press "Tutorial booking" in the app
        When I press "Sign me up for this session" near "Slot 2" in the app
        Then I should see "You are signed up to this session"
        But I should not see "Sign me up for this session"
        When I press "Remove my signup" near "Slot 2" in the app
        Then I should see "Sign me up for this session"
        But I should not see "You are signed up to this session"

    Scenario: A student cannot sign up to a full session, but can sign up to, and remove themselves from another session.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        And I sign up to "Slot 1" in signup sheet
        And I log out
        And I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student2"
        Then I should be able to sign up to signup sheet
        But I should not be able to sign up to "Slot 1" in signup sheet
        And I should see "student1" is signed up to "Slot 1" in signup sheet
        When I sign up to "Slot 2" in signup sheet
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 2" in signup sheet
        And I should see "student1" is signed up to "Slot 1" in signup sheet
        When I remove my sign up from signup sheet
        Then I should be able to sign up to signup sheet
        And I should see "student1" is signed up to "Slot 1" in signup sheet

    Scenario: A participant should be able to cancel removing their signup
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        When I sign up to "Slot 2" in signup sheet
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 2" in signup sheet
        When I cancel remove my sign up from signup sheet
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 2" in signup sheet
