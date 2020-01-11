@mod @mod_tutorialbooking @uon
Feature: Teachers can remove participants from sessions.
    In order to effectively manage sessions
    As a teacher
    I need to be able to remove a participant from a session

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
        And the following "activities" exist:
            | activity        | course | idnumber | name             | intro                           |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking |
        And I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And I add a new session to signup sheet with:
            | Title | Slot 1 |
            | Number of places | 2 |
            | Position | Top of the page |
        And I add a new session to signup sheet with:
            | Title | Slot 2 |
            | Number of places | 1 |
            | Position | Bottom of the page |
        And in "Slot 1" of signup sheet I add:
            | student1 |
            | student3 |
        And in "Slot 2" of signup sheet I add:
            | student2 |
        And I log out

    Scenario: As a teacher I can remove a participant from a session, the participant should see the explanation for the removal.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        # Remove a student, and check only they are removed.
        When I remove "student1" from "Slot 1" of signup sheet with "I am removing you" as a reason
        Then there should be "1" free places of "2" total places available on "Slot 1" of signup sheet
        And there should be "0" free places of "1" total places available on "Slot 2" of signup sheet
        And I should see "student3" is signed up to "Slot 1" in signup sheet
        # Remove a second student.
        When I remove "student2" from "Slot 2" of signup sheet with "I just feel like it" as a reason
        Then there should be "1" free places of "2" total places available on "Slot 1" of signup sheet
        And there should be "1" free places of "1" total places available on "Slot 2" of signup sheet
        And I should see "student3" is signed up to "Slot 1" in signup sheet
        # Check that the teacher can see the removal messages in the history.
        When I view sent messages in signup sheet I should see:
            | message             | sender   |
            | I am removing you   | teacher1 |
            | I just feel like it | teacher1 |
        And I log out
        # Check that the first removed student can sign up to sessions and see the message.
        When I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student1"
        Then I should be able to sign up to signup sheet
        And I log out
        When I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student3"
        Then I should not be able to sign up to signup sheet
        And I log out
        When I am on the "Tutorial booking" "mod_tutorialbooking > Sessions" page logged in as "student2"
        Then I should be able to sign up to signup sheet

    Scenario: As a teacher if I cancel the remove process a participant should not be removed from the session.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        When I cancel the removal of "student1" from "Slot 1" of signup sheet with "I am removing you" as a reason
        Then there should be "0" free places of "2" total places available on "Slot 1" of signup sheet
        And I should see "student1" is signed up to "Slot 1" in signup sheet
