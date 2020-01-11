@mod @mod_tutorialbooking @uon
Feature: Sessions have settings that should be editable.
    In order to have meaning sessions must be able to have editable properties
    As a teacher
    I need to be able to change the settings of sessions.

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

    Scenario: I should be able to create a session based on an existing session
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And I should see "Slot 1" in position "1" of signup sheet
        And I should see "Slot 2" in position "2" of signup sheet
        And I create new session based on "Slot 1" in signup sheet with:
            | Title | New slot |
            | Position | Top of the page |
        Then I should see "New slot" in position "1" of signup sheet
        And there should be "1" free places of "1" total places available on "New slot" of signup sheet
        And I should see "Slot 1" in position "2" of signup sheet
        And I should see "Slot 2" in position "3" of signup sheet

    Scenario: I should be able to change the details of a session.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And I should see "Slot 1" in position "1" of signup sheet
        And I should see "Slot 2" in position "2" of signup sheet
        And there should be "1" free places of "1" total places available on "Slot 1" of signup sheet
        When I edit "Slot 1" in signup sheet with:
            | Title | Modified slot 1 |
            | Number of places | 4 |
            | Position | Bottom of the page |
        Then I should see "Slot 2" in position "1" of signup sheet
        And I should see "Modified slot 1" in position "2" of signup sheet
        And there should be "4" free places of "4" total places available on "Modified slot 1" of signup sheet

    Scenario: I should be able to delete a session.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And I should see "Slot 1" in position "1" of signup sheet
        And I should see "Slot 2" in position "2" of signup sheet
        When I delete "Slot 1" of signup sheet
        Then I should see "Slot 2" in position "1" of signup sheet

    Scenario: It should not be possible to set the number of places in a session to less than 1 or more than 30000.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        When I edit "Slot 2" in signup sheet with:
            | Number of places | 0 |
        Then there should be "1" free places of "1" total places available on "Slot 2" of signup sheet
        When I edit "Slot 2" in signup sheet with:
            | Number of places | 30001 |
        Then there should be "30000" free places of "30000" total places available on "Slot 2" of signup sheet
        When I edit "Slot 2" in signup sheet with:
            | Number of places | 2 |
        Then there should be "2" free places of "2" total places available on "Slot 2" of signup sheet
        When I edit "Slot 2" in signup sheet with:
            | Number of places | 30000 |
        Then there should be "30000" free places of "30000" total places available on "Slot 2" of signup sheet
        When I edit "Slot 2" in signup sheet with:
            | Number of places | -1 |
        Then there should be "1" free places of "1" total places available on "Slot 2" of signup sheet
        When I edit "Slot 2" in signup sheet with:
            | Number of places | 29999 |
        Then there should be "29999" free places of "29999" total places available on "Slot 2" of signup sheet

    Scenario: An editing teacher should be able to edit the number of places to be lower than the number of signups.
        Given I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And in "Slot 2" of signup sheet I add:
            | student1 |
            | student2 |
        When I edit "Slot 2" in signup sheet with:
            | Number of places | 1 |
        Then I should see that "Slot 2" with "1" places is oversubscribed by "1" in signup sheet

    Scenario: A user without the capability to oversubscribe should not be able to edit the number of places to be lower than the number of signups.
        Given the following "permission overrides" exist:
            | capability                        | permission | role           | contextlevel | reference |
            | mod/tutorialbooking:oversubscribe | Prevent    | editingteacher | Course       | C1        |
        And I am on the "Tutorial booking" "mod_tutorialbooking > Management" page logged in as "teacher1"
        And in "Slot 2" of signup sheet I add:
            | student1 |
            | student2 |
        When I edit "Slot 2" in signup sheet with:
            | Number of places | 1 |
        Then I should see I cannot reduce the places to "1" or less than "2"
