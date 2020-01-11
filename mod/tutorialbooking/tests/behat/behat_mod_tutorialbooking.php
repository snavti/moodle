<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Steps definitions related with the tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @category   test
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2014 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Behat\Exception\PendingException as PendingException;
/**
 * Tutorial booking related steps definitions.
 *
 * @package    mod_tutorialbooking
 * @category   test
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2014 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_tutorialbooking extends behat_base {
    /**
     * Generates the xpath required to select a named slot in a page.
     *
     * @param string $slotname
     * @return string The xpath selector.
     */
    protected function generate_xpath_slot_selector($slotname) {
        // XPath to a tutorial booking slot.
        // The selector is looking to select an element with the .tutorial_session class
        // that contains an element with the .sectionname class containing the defined slotname.
        return "//*[contains(concat(' ', normalize-space(@class), ' '), ' tutorial_session ') "
                . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' sectionname ') "
                . "and text()[contains(., '" .$this->escape($slotname). "')]]]";
    }

    /**
     * Generates the xpath required to select the controls for a session.
     *
     * @param type $sessionname
     * @return string The xpath selector.
     */
    protected function generate_xpath_session_controls($sessionname) {
        $sessionxpath = $this->generate_xpath_slot_selector($sessionname);
        return $sessionxpath . "//*[contains(concat(' ', normalize-space(@class), ' '), ' controls ')]";
    }

    /**
     * Adds a session to a signup sheet specified by its name, must be used on the management page.
     *
     * @When /^I add a new session to signup sheet with:$/
     * @param \Behat\Gherkin\Node\TableNode $settings
     * @return void
     */
    public function i_add_a_new_timeslot_to_signup_sheet_with(TableNode $settings) {
        $this->execute('behat_general::click_link', array(get_string('newtimslotprompt', 'mod_tutorialbooking')));
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', array($settings));
        $this->execute('behat_forms::press_button', array(get_string('saveasnew', 'mod_tutorialbooking')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Presses cancel on the removal confirmation form for a user with the supplied username on a named session of the named signup sheet.
     *
     * Must be used from the management page.
     *
     * @When /^I cancel the removal of "(?P<username_string>(?:[^"]|\\")*)" from "(?P<session_name_string>(?:[^"]|\\")*)" of signup sheet with "(?P<message_string>(?:[^"]|\\")*)" as a reason$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $username The user4name of a Moodle user to be removed.
     * @param string $slotname The name of a slot in the tutorial the user is in.
     * @param string $message The message that should be entered into the confirmtion removal form.
     * @return void
     */
    public function i_cancel_the_removal_of_student_from_session_of_signup_sheet_with_reason($username, $slotname, $message) {
        global $DB;

        // Get the details of the user.
        $name = $DB->get_field('user', $DB->sql_concat_join("' '", array('firstname', 'lastname')), array('username' => $username));

        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $this->execute('behat_general::i_click_on_in_the', array($name, 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::i_set_the_field_to', array('message[text]', $message));
        $this->execute('behat_general::i_click_on', array(get_string('cancel'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Create a session by editing the session name in a named signup sheet and clicking 'Save as new'
     *
     * @When /^I create new session based on "(?P<session_name_string>(?:[^"]|\\")*)" in signup sheet with:$/
     * @param string $slotname
     * @param \Behat\Gherkin\Node\TableNode $settings
     * @return void
     */
    public function i_create_new_session_based_on_in_signup_sheet_with($slotname, TableNode $settings) {
        $controlsselector = $this->generate_xpath_session_controls($slotname);
        $editstring = get_string('editsession', 'mod_tutorialbooking');
        if ($this->running_javascript()) {
            $this->execute('behat_action_menu::i_open_the_action_menu_in', [$controlsselector, 'xpath_element']);
            $this->execute('behat_action_menu::i_choose_in_the_open_action_menu', [$editstring]);
        } else {
            $this->execute('behat_general::i_click_on_in_the', [$editstring, 'link', $controlsselector, 'xpath_element']);
        }
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', array($settings));
        $this->execute('behat_forms::press_button', array(get_string('saveasnew', 'mod_tutorialbooking')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Delete a named session from a named signup sheet. Must be used on management page.
     *
     * @When /^I delete "(?P<session_name_string>(?:[^"]|\\")*)" of signup sheet$/
     * @param string $slotname The name of a time slot.
     * @return void
     */
    public function i_delete_session_of_signup_sheet($slotname) {
        $controlsselector = $this->generate_xpath_session_controls($slotname);
        $deletestring = get_string('deletesession', 'mod_tutorialbooking');
        if ($this->running_javascript()) {
            $this->execute('behat_action_menu::i_open_the_action_menu_in', [$controlsselector, 'xpath_element']);
            $this->execute('behat_action_menu::i_choose_in_the_open_action_menu', [$deletestring]);
        } else {
            $this->execute('behat_general::i_click_on_in_the', [$deletestring, 'link', $controlsselector, 'xpath_element']);
        }
        $this->execute('behat_forms::press_button', array(get_string('continue')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Change the settings of a named session in a named signup sheet activity. Must be used on management page.
     *
     * @When /^I edit "(?P<session_name_string>(?:[^"]|\\")*)" in signup sheet with:$/
     * @param string $slotname The name of a timeslot.
     * @param \Behat\Gherkin\Node\TableNode $settings The settings to be changed for the tutorial booking.
     * @return void
     */
    public function i_edit_session_in_signup_sheet_with($slotname, TableNode $settings) {
        $controlsselector = $this->generate_xpath_session_controls($slotname);
        $editstring = get_string('editsession', 'mod_tutorialbooking');
        if ($this->running_javascript()) {
            $this->execute('behat_action_menu::i_open_the_action_menu_in', [$controlsselector, 'xpath_element']);
            $this->execute('behat_action_menu::i_choose_in_the_open_action_menu', [$editstring]);
        } else {
            $this->execute('behat_general::i_click_on_in_the', [$editstring, 'link', $controlsselector, 'xpath_element']);
        }
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', array($settings));
        $this->execute('behat_forms::press_button', array(get_string('save', 'mod_tutorialbooking')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * The user will remove the users signup. Must be used on the sessions page.
     *
     * @When /^I remove my sign up from signup sheet$/
     * @return void
     */
    public function i_remove_my_sign_up_from_signup_sheet() {
        $this->execute('behat_general::i_click_on', array(get_string('removefromslot', 'mod_tutorialbooking'), 'link'));
        $this->execute('behat_general::i_click_on', array(get_string('continue'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Click on the remove from from session button, but cancel the process at the confirmation screen.
     *
     * Must be used from the session page.
     *
     * @When /^I cancel remove my sign up from signup sheet$/
     * @return void
     */
    public function i_cancel_remove_my_sign_up_from_signup_sheet() {
        $this->execute('behat_general::i_click_on', array(get_string('removefromslot', 'mod_tutorialbooking'), 'link'));
        $this->execute('behat_general::i_click_on', array(get_string('cancel'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Remove a participant by username from a named session on a signup sheet sending them a message.
     *
     * Must be used on the management page of a signup sheet.
     *
     * @When /^I remove "(?P<username_string>(?:[^"]|\\")*)" from "(?P<session_name_string>(?:[^"]|\\")*)" of signup sheet with "(?P<message_string>(?:[^"]|\\")*)" as a reason$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $username The username of the user to be removed.
     * @param string $slotname The name of the timeslot the user is to be removed from.
     * @param string $message The message describing why the user was removed.
     * @return void
     */
    public function i_remove_user_from_session_of_signup_sheet_with_message_as_a_reason($username, $slotname, $message) {
        global $DB;

        // Get the details of the user.
        $name = $DB->get_field('user', $DB->sql_concat_join("' '", array('firstname', 'lastname')), array('username' => $username));

        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $this->execute('behat_general::i_click_on_in_the', array($name, 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::i_set_the_field_to', array('message[text]', $message));
        $this->execute('behat_general::i_click_on', array(get_string('remove', 'mod_tutorialbooking'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Check that the user can sign up to any session in the signup sheet.
     *
     * Must be on the sessions page of a signup sheet.
     *
     * @Then /^I should be able to sign up to signup sheet$/
     * @return void
     */
    public function i_should_be_able_to_sign_up_to_signup_sheet() {
        $this->execute('behat_general::assert_page_contains_text', array(get_string('signupforslot', 'mod_tutorialbooking')));
    }

    /**
     * Check that the user is signed up, but cannot see a link to remove themselves from a signup sheet activity.
     *
     * Must be on the session page of a signup sheet.
     *
     * @Then /^I should not be able to remove my sign up from signup sheet$/
     * @param string $tutorialname The name of the tutorial booking activity.
     * @return void
     */
    public function i_should_not_be_able_to_remove_my_sign_up_from_signup_sheet() {
        $signedupstring = get_string('you', 'mod_tutorialbooking');
        $removestring = get_string('removefromslot', 'mod_tutorialbooking');
        $this->execute('behat_general::assert_element_contains_text', array($signedupstring, '.tutorial_sessions', 'css_element'));
        $this->execute('behat_general::assert_element_not_contains_text', array($removestring, '.tutorial_sessions', 'css_element'));
    }

    /**
     * The user should not be able to see the participant lists for the signup sheet activity.
     *
     * Must be on the session page of a signup sheet.
     *
     * @Then /^I should not be able to see signups on signup sheet$/
     * @return void
     */
    public function i_should_not_be_able_to_see_signups_on_signup_sheet() {
        $this->execute('behat_general::should_not_exist_in_the', array('.signedupuser', 'css_element', '.tutorial_sessions', 'css_element'));
    }

    /**
     * The user should not be able to sign up to the named session in the signup sheet activity.
     *
     * Must be on the session page of a signup sheet.
     *
     * @Then /^I should not be able to sign up to "(?P<session_name_string>(?:[^"]|\\")*)" in signup sheet$/
     * @param string $slotname The name of a tutorial booking slot to be checked.
     * @return void
     */
    public function i_should_not_be_able_to_sign_up_to_session_in_signup_sheet($slotname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $signupstring = get_string('signupforslot', 'mod_tutorialbooking');
        $this->execute('behat_general::assert_element_not_contains_text', array($signupstring, $slotselector, 'xpath_element'));
    }

    /**
     * Check that the signup link does not appear on a signup sheet.
     *
     * Must be used on session page of a signup sheet.
     *
     * @Then /^I should not be able to sign up to signup sheet$/
     * @return void
     */
    public function i_should_not_be_able_to_sign_up_to_signup_sheet() {
        $this->execute('behat_general::assert_page_not_contains_text', array(get_string('signupforslot', 'mod_tutorialbooking')));
    }

    /**
     * Check that a user by username is signed up to a specific session in a signup sheet.
     *
     * Must be used on a session page of a signup sheet.
     *
     * @Then /^I should see I am signed up to "(?P<session_name_string>(?:[^"]|\\")*)" in signup sheet$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $slotname The name of the timeslot.
     * @return void
     */
    public function i_should_see_i_am_signed_up_to_session_in_signup_sheet($slotname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $signedupstring = get_string('you', 'mod_tutorialbooking');
        $this->execute('behat_general::assert_element_contains_text', array($signedupstring, $slotselector, 'xpath_element'));
    }

    /**
     * Check that the edit spaces error message is displayed correctly.
     *
     * @Then /^I should see I cannot reduce the places to "(?P<set_to_number>\d+)" or less than "(?P<totalsignups_number>\d+)"$/
     * @param int $setto The number of spaces the user tried to set.
     * @param int $numberofsignups The number of signups to the timeslot.
     * @return void
     */
    public function i_should_see_i_cannot_reduce_the_spaces_to_or_less_than($setto, $numberofsignups) {
        $errorstring = get_string('editspaceserror', 'mod_tutorialbooking', array('spaces' => (int)$setto, 'signedup' => (int)$numberofsignups));
        $this->execute('behat_general::assert_element_contains_text', array($errorstring, '#notice', 'css_element'));
    }

    /**
     * Check that a user by username is a participant on a specific session in a signup sheet.
     *
     * @Then /^I should see "(?P<user_name_string>(?:[^"]|\\")*)" is signed up to "(?P<session_name_string>(?:[^"]|\\")*)" in signup sheet$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $username The username of the person to find.
     * @param string $slotname The name of the session.
     * @return void
     */
    public function i_should_see_is_signed_up_to_slot_in_signup_sheet($username, $slotname) {
        global $DB;
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        // Get the user's name from the database.
        $name = $DB->get_field('user', $DB->sql_concat_join("' '", ['firstname', 'lastname']), ['username' => $username]);
        $this->execute('behat_general::assert_element_contains_text', array($name, $slotselector, 'xpath_element'));
    }

    /**
     * Tests if a named session is in the correct position of a signup sheet activity.
     *
     * @Then /^I should see "(?P<session_name_string>(?:[^"]|\\")*)" in position "(?P<position_number>\d+)" of signup sheet$/
     * @param string $slotname The name of the timeslot.
     * @param int $position The position of the timeslot, starting at 1.
     * @return void
     */
    public function i_should_see_session_in_position_of_signup_sheet($slotname, $position) {
        $slotselector = "//*[contains(concat(' ', normalize-space(@class), ' '), ' tutorial_session ') "
                . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' sectionname ')]]"
                . "[".(int)$position."]";
        $this->execute('behat_general::assert_element_contains_text', array($this->escape($slotname), $slotselector, 'xpath_element'));
    }

    /**
     * For a named session in a signup sheet, check that the session has the
     * expected number of places and is oversubscribed by the given number of participants.
     *
     * @Then /^I should see that "(?P<session_name_string>(?:[^"]|\\")*)" with "(?P<totalspaces_number>\d+)" places is oversubscribed by "(?P<oversubscribed_number>\d+)" in signup sheet$/
     * @param string $slotname The name of the timeslot to be checked.
     * @param int $totalspaces The spaces the timeslot is set to have.
     * @param int $oversubscribed The number of users subscribed above the total.
     * @return void
     */
    public function i_should_see_that_session_is_oversubscribed_in_signup_sheet($slotname, $totalspaces, $oversubscribed) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $spaces = array(
            'total' => $totalspaces,
            'taken' => (int)$totalspaces + (int)$oversubscribed,
            'left' => $oversubscribed
        );
        $oversubscribedtext = get_string('numbersline_oversubscribed', 'mod_tutorialbooking', $spaces);
        $this->execute('behat_general::assert_element_contains_text', array($oversubscribedtext, $slotselector, 'xpath_element'));
    }

    /**
     * The user adds themselves to a named session in a signup sheet.
     *
     * Must be on the session page of a signup sheet.
     *
     * @When /^I sign up to "(?P<session_name_string>(?:[^"]|\\")*)" in signup sheet$/
     * @param string $slotname The name of the slot the user should add themselves to.
     * @return void
     */
    public function i_sign_up_to_session_in_signup_sheet($slotname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $signupstring = get_string('signupforslot', 'mod_tutorialbooking');
        $this->execute('behat_general::i_click_on_in_the', array($signupstring, 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Checks that the listed messages are visible in the signup sheet.
     *
     * Must be on the management page of a signup sheet.
     *
     * @When /^I view sent messages in signup sheet I should see:$/
     * @param \Behat\Gherkin\Node\TableNode $messages The messages that should be present. The following parameters are valid:
     *              * message - The text of the message body (required)
     *              * sender - The username of the sender (optional)
     * @return void
     */
    public function i_view_sent_messages_in_signup_sheet_i_should_see(TableNode $messages) {
        $this->execute('behat_general::click_link', array(get_string('viewmessages', 'mod_tutorialbooking')));

        foreach ($messages->getHash() as $message) {
            // XPath selector for a message.
            // The selector is looking for a .message element that
            // contains a .messagebody element with the text of the message.
            $messageselector = "//*[contains(concat(' ', normalize-space(@class), ' '), ' message ') "
                    . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' messagebody ') "
                    . "and (text()[contains(., '" .$this->escape($message['message']). "')]"
                    . "or .//*[text()[contains(., '" .$this->escape($message['message']). "')]])]]";
            $this->execute('behat_general::assert_element_contains_text', array($message['message'], $messageselector, 'xpath_element'));

            if (isset($message['sender'])) {
                // XPath selector for a sender in a message.
                // The selector looking for the .messageheader .text element that
                // is preceeded by a .messageheader .description with the localised
                // text of 'Sender'.
                $senderselector = $messageselector . "/descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' messageheader ') "
                        . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' description ') "
                        . "and text()[contains(., '" . get_string('sentby', 'mod_tutorialbooking') . "')]]]"
                        . "/descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' text ')]";
                $this->execute('behat_general::assert_element_contains_text', array($message['sender'], $senderselector, 'xpath_element'));
            }
        }
    }

    /**
     * Adds users to a named session in a signup sheet, using the teachers force adding functionality.
     *
     * Must be on the management page of a signup sheet.
     *
     * @When /^in "(?P<session_name_string>(?:[^"]|\\")*)" of signup sheet I add:$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $slotname The name of the slot users should be added to.
     * @param \Behat\Gherkin\Node\TableNode $users The list of users that should be added to the tutorial booking.
     * @return void
     */
    public function in_session_of_signup_sheet_i_add($slotname, TableNode $users) {
        global $DB;

        $controlsselector = $this->generate_xpath_session_controls($slotname);

        // Get the id's of the users we wish to add.
        $params = array();
        $insql = array();
        $identifier = 0;
        foreach ($users->getRows() as $user) {
            $insql[] = ':user' . $identifier;
            $params['user' . $identifier++] = $user[0];
        }
        // The users are identified by their user id on the form, so we can use that to select them.
        $insql = implode(',', $insql);
        $userids = $DB->get_fieldset_select('user', 'id', "username IN ($insql)", $params);

        $addstudents = get_string('addstudents', 'mod_tutorialbooking');
        if ($this->running_javascript()) {
            $this->execute('behat_action_menu::i_open_the_action_menu_in', [$controlsselector, 'xpath_element']);
            $this->execute('behat_action_menu::i_choose_in_the_open_action_menu', [$addstudents]);
        } else {
            $this->execute('behat_general::i_click_on_in_the', [$addstudents, 'link', $controlsselector, 'xpath_element']);
        }
        $this->execute('behat_forms::i_set_the_field_to', ['addtosession[]', implode(',', $userids)]);
        $this->execute('behat_general::i_click_on', [$addstudents, 'button']);
        $this->execute('behat_general::i_wait_to_be_redirected', []);
    }

    /**
     * Generates the url for the session management page.
     *
     * @param string $identifier The tutorial booking activity name.
     * @return \moodle_url
     */
    protected function resolve_management_page_url(string $identifier): moodle_url {
        $tutorialid = $this->resolve_tutiorial_id_from_name($identifier);
        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorialid);
        return new moodle_url('/mod/tutorialbooking/view.php', ['tutorialid' => $tutorialid, 'courseid' => $cm->course]);
    }

    /**
     * Convert page names to URLs for steps.
     *
     * Recognised page names are:
     * | Page type     | Identifier meaning | description                                     |
     * | Sessions      | Signup sheet name  | The page students use to signup to sessions     |
     * | Management    | Signup sheet name  | The page that is used to manage sessions        |
     *
     * @param string $type identifies which type of page this is, e.g. 'Attempt review'.
     * @param string $identifier identifies the particular page, e.g. 'Test quiz > student > Attempt 1'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        switch ($type) {
            case 'Sessions':
                return $this->resolve_session_page_url($identifier);
            case 'Management':
                return $this->resolve_management_page_url($identifier);
            default:
                throw new Exception('Unrecognised signup sheet page type "' . $type . '."');
        }
    }

    /**
     * Generates the url for the student session page.
     *
     * The URL should not redirect users who have management capabilities.
     *
     * @param string $identifier The tutorial booking activity name.
     * @return \moodle_url
     */
    protected function resolve_session_page_url(string $identifier): moodle_url {
        $tutorialid = $this->resolve_tutiorial_id_from_name($identifier);
        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorialid);
        return new moodle_url('/mod/tutorialbooking/view.php', ['id' => $cm->id, 'redirect' => 0]);
    }

    /**
     * Gets the id of a tutorial booking from the name.
     *
     * @global moodle_database $DB
     * @param string $name
     * @return int
     * @throws Exception
     */
    protected function resolve_tutiorial_id_from_name(string $name): int {
        global $DB;
        $tutorialid = $DB->get_field('tutorialbooking', 'id', ['name' => trim($name)]);
        if (!$tutorialid) {
            throw new Exception("The specified signup sheet with name '$name' does not exist");
        }
        return $tutorialid;
    }

    /**
     * Test that the number of free places is reported correctly for a named session on a signup sheet activity.
     *
     * @Then /^there should be "(?P<freeplaces_number>\d+)" free places of "(?P<totalplaces_number>\d+)" total places available on "(?P<session_name_string>(?:[^"]|\\")*)" of signup sheet$/
     * @param int $freespaces The number of free spaces expected.
     * @param int $totalspaces The total spaces on the timeslot.
     * @param string $slotname The name of the timeslot.
     * @return void
     */
    public function there_should_be_free_space_of_total_spaces_available_on_session_of_signup_sheet($freespaces, $totalspaces, $slotname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $spaces = array(
            'total' => $totalspaces,
            'taken' => (int)$totalspaces - (int)$freespaces,
            'left' => $freespaces
        );
        $spacestext = get_string('numbersline', 'mod_tutorialbooking', $spaces);
        $this->execute('behat_general::assert_element_contains_text', array($spacestext, $slotselector, 'xpath_element'));
    }
}
