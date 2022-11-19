@mod @mod_tutorialbooking @uon
Feature: Teachers can add students to session.
    In order to assign students to a specific session
    As a teacher
    I need to be able to add a student to a session

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | teacher2 | Teacher   | 2        | teacher2@example.com |
            | student1 | Student   | 1        | student1@example.com |
            | student2 | Student   | 2        | student2@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | teacher2 | C1     | teacher        |
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
            | Number of places | 1 |
            | Position | Bottom of the page |
        And I log out

    Scenario: Teachers should be able to add a student to a session
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And in "Slot 1" of signup sheet I add:
            | student1 |
        Then I should see "student1" is signed up to "Slot 1" in signup sheet
        When I log out
        And I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 1" in signup sheet

    Scenario: Editing teachers should be able to add a student to a full session
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And in "Slot 1" of signup sheet I add:
            | student1 |
            | student2 |
        Then I should see "student1" is signed up to "Slot 1" in signup sheet
        And I should see "student2" is signed up to "Slot 1" in signup sheet
        And I should see that "Slot 1" with "1" places is oversubscribed by "1" in signup sheet
        When I log out
        And I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 1" in signup sheet
        And I should see "student2" is signed up to "Slot 1" in signup sheet
        When I log out
        And I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student2"
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 1" in signup sheet
        And I should see "student1" is signed up to "Slot 1" in signup sheet
