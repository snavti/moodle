<?php
// This file is part of the Tutorial Booking activity.
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
 * Tests the tutorial booking Privacy API implementation.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2018
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_tutorialbooking\privacy\provider;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests the tutorial booking privacy provider class.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2018
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_privacy_provider_test extends \core_privacy\tests\provider_testcase {
    /** @var mod_tutorialbooking_generator The tutorial booking data generator. */
    protected $tutorialgenerator;

    /**
     * Setup for each test.
     */
    public function setUp() {
        parent::setUp();
        $this->tutorialgenerator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $this->resetAfterTest(true);
    }

    /**
     * Run at the end of each test.
     */
    public function tearDown() {
        $this->assertDebuggingNotCalled();
        parent::tearDown();
    }

    /**
     * Test a user with no signups or message sends.
     */
    public function test_user_not_signed_up() {
        $user = self::getDataGenerator()->create_user();
        $otheruser = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');
        $tutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $slot = $this->tutorialgenerator->add_slot($tutorial);
        $this->tutorialgenerator->signup_user($tutorial, $slot, $otheruser);
        // Test that no contexts were retrieved.
        $contextlist = $this->get_contexts_for_userid($user->id, 'mod_tutorialbooking');
        $contexts = $contextlist->get_contextids();
        $this->assertCount(0, $contexts);
    }

    /**
     * Tests a user is signed up but has no message sends.
     */
    public function test_signedup_user() {
        $user = self::getDataGenerator()->create_user();
        $otheruser = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');
        // Sign up the user to a tutorial booking.
        $tutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $slot = $this->tutorialgenerator->add_slot($tutorial);
        $this->tutorialgenerator->signup_user($tutorial, $slot, $user);
        // Signup another user to a different tutorial booking.
        $othertutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $otherslot = $this->tutorialgenerator->add_slot($othertutorial);
        $this->tutorialgenerator->signup_user($othertutorial, $otherslot, $otheruser);
        
        // Test that contexts were retrieved.
        $contextlist = $this->get_contexts_for_userid($user->id, 'mod_tutorialbooking');
        $contexts = $contextlist->get_contextids();
        $this->assertCount(1, $contexts);

        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorial->id);
        $context = \context_module::instance($cm->id);
        $this->assertEquals($context, $contextlist->current());

        // Test export.
        $this->export_context_data_for_user($user->id, $context, 'mod_tutorialbooking');
        $writer = \core_privacy\local\request\writer::with_context($context);
        $this->assertTrue($writer->has_any_data());
        $signupcontext = [get_string('privacy:export:signups', 'mod_tutorialbooking')];
        $this->assertNotEmpty($writer->get_data($signupcontext));
        $messagecontext = [get_string('privacy:export:messages', 'mod_tutorialbooking')];
        $this->assertFalse($writer->has_any_data($messagecontext));
    }

    /**
     * Tests that a user who has sent a message but not signed up.
     */
    public function test_user_sent_message() {
        $user = self::getDataGenerator()->create_user();
        $otheruser1 = self::getDataGenerator()->create_user();
        $otheruser2 = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');
        self::getDataGenerator()->enrol_user($otheruser1->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($otheruser2->id, $course->id, 'teacher');
        // Sign up the user to a tutorial booking.
        $tutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $slot = $this->tutorialgenerator->add_slot($tutorial);
        $this->tutorialgenerator->signup_user($tutorial, $slot, $otheruser1);
        $this->setUser($user);
        $this->tutorialgenerator->create_message($tutorial, $slot);
        $this->setAdminUser();
        $othertutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $otherslot = $this->tutorialgenerator->add_slot($othertutorial);
        $this->tutorialgenerator->signup_user($othertutorial, $otherslot, $otheruser1);
        $this->setUser($otheruser2);
        $this->tutorialgenerator->create_message($othertutorial, $otherslot);
        $this->setAdminUser();

        // Test that contexts were retrieved.
        $contextlist = $this->get_contexts_for_userid($user->id, 'mod_tutorialbooking');
        $contexts = $contextlist->get_contextids();
        $this->assertCount(1, $contexts);

        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorial->id);
        $context = \context_module::instance($cm->id);
        $this->assertEquals($context, $contextlist->current());

        // Test export.
        $this->export_context_data_for_user($user->id, $context, 'mod_tutorialbooking');
        $writer = \core_privacy\local\request\writer::with_context($context);
        $this->assertTrue($writer->has_any_data());
        $signupcontext = [get_string('privacy:export:signups', 'mod_tutorialbooking')];
        $this->assertEmpty($writer->get_data($signupcontext));
        $messagecontext = [get_string('privacy:export:messages', 'mod_tutorialbooking')];
        $this->assertTrue($writer->has_any_data($messagecontext));
    }

    /**
     * Ensure that all user data is deleted from a context.
     */
    public function test_all_users_deleted_from_context() {
        global $DB;
        // Setup data.
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($user3->id, $course->id, 'editingteacher');
        $tutorial1 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $tutorial1cm = get_coursemodule_from_instance('tutorialbooking', $tutorial1->id);
        $tutorial1context = \context_module::instance($tutorial1cm->id);
        $t1slot1 = $this->tutorialgenerator->add_slot($tutorial1);
        $t1slot2 = $this->tutorialgenerator->add_slot($tutorial1);
        $this->tutorialgenerator->signup_user($tutorial1, $t1slot1, $user1);
        $this->tutorialgenerator->signup_user($tutorial1, $t1slot2, $user2);
        $tutorial2 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $t2slot1 = $this->tutorialgenerator->add_slot($tutorial2);
        $this->tutorialgenerator->signup_user($tutorial2, $t2slot1, $user1);
        // Create some messages.
        $this->setUser($user3);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot1);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot2);
        $this->tutorialgenerator->create_message($tutorial2, $t2slot1);
        // Make the call to delete.
        provider::delete_data_for_all_users_in_context($tutorial1context);
        // Check that the user data is deleted, but not the structure.
        $this->assertEquals(0, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial1->id]));
        $this->assertEquals(0, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial1->id]));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial1->id]));
        // Check that the other Tutorial has not been affected.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial2->id]));
    }

    /**
     * Ensure that all user data is deleted for a specific context, when the user signed up to slots.
     */
    public function test_delete_data_for_user() {
        global $DB;
        // Setup data.
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($user3->id, $course->id, 'editingteacher');
        $tutorial1 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $tutorial1cm = get_coursemodule_from_instance('tutorialbooking', $tutorial1->id);
        $tutorial1context = \context_module::instance($tutorial1cm->id);
        $t1slot1 = $this->tutorialgenerator->add_slot($tutorial1);
        $t1slot2 = $this->tutorialgenerator->add_slot($tutorial1);
        $this->tutorialgenerator->signup_user($tutorial1, $t1slot1, $user1);
        $this->tutorialgenerator->signup_user($tutorial1, $t1slot2, $user2);
        $tutorial2 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $t2slot1 = $this->tutorialgenerator->add_slot($tutorial2);
        $this->tutorialgenerator->signup_user($tutorial2, $t2slot1, $user1);
        // Create some messages.
        $this->setUser($user3);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot1);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot2);
        $this->tutorialgenerator->create_message($tutorial2, $t2slot1);
        // Make the call to delete.
        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($user1->id),
            'mod_tutorialbooking',
            [$tutorial1context->id]
        );
        provider::delete_data_for_user($approvedcontextlist);
        // Check that the user's data has been deleted.
        $params1 = ['tutorialid' => $tutorial1->id, 'userid' => $user1->id];
        $params2 = ['tutorialbookingid' => $tutorial1->id, 'sentby' => $user1->id];
        $this->assertEquals(0, $DB->count_records('tutorialbooking_signups', $params1));
        $this->assertEquals(0, $DB->count_records('tutorialbooking_messages', $params2));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial1->id]));
        // Check other users data on the tutorial booking has not been affected.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial1->id]));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial1->id]));
        // Check that the other Tutorial has not been affected.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial2->id]));
    }

    /**
     * Ensure that all user data is deleted for a specific context when a user has sent messages.
     */
    public function test_delete_data_for_user_messages() {
        global $DB;
        // Setup data.
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($user2->id, $course->id, 'editingteacher');
        self::getDataGenerator()->enrol_user($user3->id, $course->id, 'editingteacher');
        $tutorial1 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $tutorial1cm = get_coursemodule_from_instance('tutorialbooking', $tutorial1->id);
        $tutorial1context = \context_module::instance($tutorial1cm->id);
        $t1slot1 = $this->tutorialgenerator->add_slot($tutorial1);
        $t1slot2 = $this->tutorialgenerator->add_slot($tutorial1);
        $this->tutorialgenerator->signup_user($tutorial1, $t1slot1, $user1);
        $tutorial2 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $t2slot1 = $this->tutorialgenerator->add_slot($tutorial2);
        $this->tutorialgenerator->signup_user($tutorial2, $t2slot1, $user1);
        // Create some messages.
        $this->setUser($user2);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot1);
        $this->setUser($user3);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot2);
        $this->tutorialgenerator->create_message($tutorial2, $t2slot1);
        // Make the call to delete.
        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($user3->id),
            'mod_tutorialbooking',
            [$tutorial1context->id]
        );
        provider::delete_data_for_user($approvedcontextlist);
        // Check that the user's data has been deleted.
        $params1 = ['tutorialid' => $tutorial1->id, 'userid' => $user3->id];
        $params2 = ['tutorialbookingid' => $tutorial1->id, 'sentby' => $user3->id];
        $this->assertEquals(0, $DB->count_records('tutorialbooking_signups', $params1));
        $this->assertEquals(0, $DB->count_records('tutorialbooking_messages', $params2));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial1->id]));
        // Check other users data on the tutorial booking has not been affected.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial1->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial1->id]));
        // Check that the other Tutorial has not been affected.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial2->id]));
    }

    /**
     * Test that all users with data in the context can be found.
     */
    public function test_get_users_in_context() {
        $signedupuser = self::getDataGenerator()->create_user();
        $messageuser = self::getDataGenerator()->create_user();
        $otheruser = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($signedupuser->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($messageuser->id, $course->id, 'editingteacher');
        self::getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');
        // Signup a user and send a message from a user.
        $tutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $slot = $this->tutorialgenerator->add_slot($tutorial);
        $this->tutorialgenerator->signup_user($tutorial, $slot, $signedupuser);
        $this->setUser($messageuser);
        $this->tutorialgenerator->create_message($tutorial, $slot);
        $this->setAdminUser();
        // Signup another user to a different tutorial booking.
        $othertutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $otherslot = $this->tutorialgenerator->add_slot($othertutorial);
        $this->tutorialgenerator->signup_user($othertutorial, $otherslot, $otheruser);

        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorial->id);
        $context = \context_module::instance($cm->id);

        // Run the test.
        $userlist = new \core_privacy\local\request\userlist($context, 'tutorialbooking');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();
        $this->assertCount(2, $userids);
        $this->assertTrue(in_array($signedupuser->id, $userids));
        $this->assertTrue(in_array($messageuser->id, $userids));
    }

    /**
     * Test that all data for a selected list of users in a context are deleted.
     *
     * @global moodle_database $DB
     */
    public function test_delete_data_for_users() {
        global $DB;
        // Setup data.
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        self::getDataGenerator()->enrol_user($user3->id, $course->id, 'editingteacher');
        $tutorial1 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $tutorial1cm = get_coursemodule_from_instance('tutorialbooking', $tutorial1->id);
        $tutorial1context = \context_module::instance($tutorial1cm->id);
        $t1slot1 = $this->tutorialgenerator->add_slot($tutorial1);
        $t1slot2 = $this->tutorialgenerator->add_slot($tutorial1);
        $this->tutorialgenerator->signup_user($tutorial1, $t1slot1, $user1);
        $this->tutorialgenerator->signup_user($tutorial1, $t1slot2, $user2);
        $tutorial2 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course->id));
        $t2slot1 = $this->tutorialgenerator->add_slot($tutorial2);
        $this->tutorialgenerator->signup_user($tutorial2, $t2slot1, $user1);
        // Create some messages.
        $this->setUser($user3);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot1);
        $this->tutorialgenerator->create_message($tutorial1, $t1slot2);
        $this->tutorialgenerator->create_message($tutorial2, $t2slot1);

        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorial1->id);
        $context = \context_module::instance($cm->id);

        // Verify the data.
        $this->assertEquals(2, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial1->id]));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial1->id]));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial1->id]));

        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial2->id]));

        $userlist = new \core_privacy\local\request\approved_userlist($context, 'tutorialbooking', [$user1->id, $user3->id]);
        provider::delete_data_for_users($userlist);

        // Check that the correct data been deleted.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial1->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial1->id, 'userid' => $user2->id]));
        $this->assertEquals(0, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial1->id]));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial1->id]));

        // Should be no changes.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', ['tutorialid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorial2->id]));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_sessions', ['tutorialid' => $tutorial2->id]));
    }
}
