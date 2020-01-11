@mod @mod_tutorialbooking @uon
Feature: Signup sheets can be set so that students cannot see who is signed up for session.
    In order to allow students to sign up to sessions that may reveal a sensitive personal status about them
    As a student
    I should not see details of participants signed up to a signup sheet, that is set to allow me to see only my own sign up.

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | student1 | Student   | 1        | student1@example.com |
            | student2 | Student   | 2        | student2@example.com |
            | student3 | Student   | 3        | student3@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | student1 | C1     | student        |
            | student2 | C1     | student        |
            | student3 | C1     | student        |
        # A privacy value of 2 means that signups are hidden from students.
        And the following "activities" exist:
            | activity        | course | idnumber | name             | intro                           | privacy |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking | 2       |
        And I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And I add a new session to signup sheet with:
            | Title | Slot 1 |
            | Number of places | 2 |
            | Position | Top of the page |
        And I add a new session to signup sheet with:
            | Title | Slot 2 |
            | Number of places | 2 |
            | Position | Bottom of the page |
        And in "Slot 1" of signup sheet I add:
            | student1 |
            | student3 |
        And in "Slot 2" of signup sheet I add:
            | student2 |
        And I log out

    Scenario: As a student I should only be able to see my own signup, I should still be able to add and remove myself to slots.
        When I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student2"
        Then I should not be able to sign up to signup sheet
        And I should see I am signed up to "Slot 2" in signup sheet
        When I remove my sign up from signup sheet
        Then I should be able to sign up to signup sheet
        And there should be "0" free places of "2" total places available on "Slot 1" of signup sheet
        And there should be "2" free places of "2" total places available on "Slot 2" of signup sheet
        But I should not be able to sign up to "Slot 1" in signup sheet
        # Because "Slot" 1 is full
        And I should not be able to see signups on signup sheet

    Scenario: As a teacher I should still be able to see everyone who is signed up.
        When I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        Then I should see "student1" is signed up to "Slot 1" in signup sheet
        And I should see "student3" is signed up to "Slot 1" in signup sheet
        And I should see "student2" is signed up to "Slot 2" in signup sheet
