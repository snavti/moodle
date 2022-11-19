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
 * Main editing view of tutorial bookings
 *
 * WARNING MUCH OF THE DATABASE CODE MAY BE VERY MYSQL SPECIFIC
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_tutorialbooking\event\course_module_edit_viewed;
use mod_tutorialbooking\event\session_message;
use mod_tutorialbooking\exception\session_exception;
use mod_tutorialbooking\output\tutorialbooking;

require('../../config.php'); // This works everytime - the one in the coding guide does not if moodle is not in the root.

// Parameters for sending messages.
$sendmessage = optional_param('sendmessage', '', PARAM_TEXT);
// Parameters for viewing messages.
$maxrecords = optional_param('messages', 5, PARAM_INT);;
$firstrecord = optional_param('page', 0, PARAM_INT);;
$viewallmessages = optional_param('filter', 0, PARAM_INT);

// These checks will generate exceptions if they do not pass.
$tutorialid = required_param('tutorialid', PARAM_INT); // Plugin instance.
$tutorial = $DB->get_record('tutorialbooking', array('id' => $tutorialid), '*', MUST_EXIST);

$courseid = required_param('courseid', PARAM_INT); // Course.

list($course, $cm) = get_course_and_cm_from_instance($tutorial->id, 'tutorialbooking', $courseid);
$tutorialbookingcontext = context_module::instance($cm->id);

require_course_login($course, false, $cm);

$PAGE->set_url( new moodle_url(str_replace($CFG->wwwroot, '', strip_querystring(qualified_me()))),
    array('tutorialid' => $tutorialid, 'courseid' => $courseid)); // Point to this page.

// Set up the page.
$PAGE->set_context($tutorialbookingcontext);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('pagetitle', 'mod_tutorialbooking'));

// The user must have the capability to view this page.
require_capability('mod/tutorialbooking:viewadminpage', $tutorialbookingcontext);

$output = $PAGE->get_renderer('mod_tutorialbooking', 'teacher');

// Get the completion object as we may need it.
$completion = new completion_info($course);

// If there is an action - do it then redirect back to this page excluding action params.
$action = optional_param('action', null, PARAM_TEXT);
if ($action) {
    $redirect = true; // Redirect after action?
    if ($action == 'edit') {
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        $sessionid = required_param('id', PARAM_INT);
        $formdata = mod_tutorialbooking_session::generate_editsession_formdata($courseid, $tutorial, $sessionid, $tutorialbookingcontext);
        $wmform = new mod_tutorialbooking_session_form(null, $formdata);
        echo $OUTPUT->header();
        $wmform->display();
        echo $OUTPUT->footer();
        $redirect = false;
    } else if ($action == 'save') {
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        if (!optional_param('cancel', '', PARAM_TEXT)) { // If the form was not cancelled.
            try {
                $sessionid = required_param('id', PARAM_INT);
                $formdata = mod_tutorialbooking_session::generate_editsession_formdata($courseid, $tutorial, $sessionid, $tutorialbookingcontext);
                $result = mod_tutorialbooking_session::update_session($tutorialid, $formdata);
            } catch (session_exception $e) {
                notice($OUTPUT->notification($e->getMessage()), $PAGE->url->out());
            }
        } // Else just keep going to redirect.
    } else if ($action == 'deletesession') {
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        $sessionid = required_param('id', PARAM_INT);
        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $sessionid), 'description', MUST_EXIST);
        $output->deleteconfirm($courseid, $tutorialid, $sessionid, strip_tags($session->description));
        $redirect = false;
    } else if ($action == 'confirmdelete') { // Confirm deletion of session.
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        $sessionid = required_param('sessionid', PARAM_TEXT);
        mod_tutorialbooking_session::delete_session($tutorialid, $sessionid);
    } else if ($action == 'moveup') { // Move a session up one space.
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        $currentposition = required_param('currentpos', PARAM_INT);
        mod_tutorialbooking_session::move_sequence_up($tutorialid, $currentposition);
    } else if ($action == 'movedown') { // Move a session down one space.
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        $currentposition = required_param('currentpos', PARAM_INT);
        mod_tutorialbooking_session::move_sequence_down($tutorialid, $currentposition);
    } else if ($action == 'emailgroup') { // This action just displays the message form.
        require_capability('mod/tutorialbooking:message', $tutorialbookingcontext);
        $id = required_param('id', PARAM_INT);
        $formdata = mod_tutorialbooking_message::generate_formdata($course, $tutorialid, $id);
        $eform = new mod_tutorialbooking_email_form(null, $formdata);
        echo $OUTPUT->header();
        $eform->display();
        echo $OUTPUT->footer();
        $redirect = false;
    } else if ($action == 'notifygroup') { // Send messages to users signed up to a session.
        require_capability('mod/tutorialbooking:message', $tutorialbookingcontext);
        if ($sendmessage) {
            $msg = required_param_array('message', PARAM_TEXT); // Array.
            $sessionid = required_param('id', PARAM_INT);
            $subject = required_param('subject', PARAM_TEXT);
            $message = mod_tutorialbooking_message::send_message($tutorial, $msg, $sessionid, $subject);
            $output->back_to_session($message, $PAGE->url);
            $redirect = false;
            $eventdata = array(
                'context' => $tutorialbookingcontext,
                'objectid' => $sessionid,
                'other' => array(
                    'tutorialid' => $tutorial->id,
                ),
            );
            $event = session_message::create($eventdata);
            $event->add_record_snapshot('course_modules', $cm);
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('tutorialbooking', $tutorial);
            $event->trigger();
        }
    } else if ($action == 'viewmessages') {
        require_capability('mod/tutorialbooking:viewadminpage', $tutorialbookingcontext);
        $PAGE->navbar->add(get_string('messagessent', 'tutorialbooking'));
        $output->displaymessagelist(mod_tutorialbooking_message::get_messages($tutorialid, $courseid, $viewallmessages,
                $firstrecord, $maxrecords));
        $redirect = false;
    } else if ($action == 'removesignup') {
        // The user is attempting to remove a student from a signup slot, first check they have the capability to do so.
        require_capability('mod/tutorialbooking:removeuser', $tutorialbookingcontext);
        // Get information about the user to be removed.
        $userid = required_param('user', PARAM_INT);
        // Dispaly a confirmation form.
        $formdata = mod_tutorialbooking_user::generate_removeuser_formdata($tutorialid, $courseid, $userid);
        $confirmform = new mod_tutorialbooking_confirmremoval_form(null, $formdata);
        if ($confirmform->is_cancelled()) {
            $redirect = true;
        } else if ($confirmform->is_submitted() && $confirmform->is_validated()) {
            // The form was submitted and not cancelled, so remove the user.
            $formdata = $confirmform->get_data();
            require_sesskey();
            $return = mod_tutorialbooking_user::remove_user($userid, $tutorial, $completion, $cm, true, $formdata->message);
        } else {
            echo $OUTPUT->header();
            $confirmform->display();
            echo $OUTPUT->footer();
            $redirect = false;
        }
    } else if ($action == 'addusers') {
        require_capability('mod/tutorialbooking:adduser', $tutorialbookingcontext);
        // Get the session.
        $id = required_param('id', PARAM_INT);
        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $id), '*', MUST_EXIST);
        // Display a form to select students to add.
        $output->display_addform($session, $tutorial);
        $redirect = false;
    } else if ($action == 'adduserconfirm') {
        require_capability('mod/tutorialbooking:adduser', $tutorialbookingcontext);
        // Get the session.
        $id = required_param('id', PARAM_INT);
        // Get the list of students selected.
        $toadd = optional_param_array('addtosession', array(), PARAM_INT);
        mod_tutorialbooking_user::addusers_from_form($courseid, $tutorial, $tutorialbookingcontext, $completion, $cm, $id, $toadd);
    } else if ($action == 'lock') {
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        mod_tutorialbooking_tutorial::togglelock($tutorialid, true);
    } else if ($action == 'unlock') {
        require_capability('mod/tutorialbooking:editsignuplist', $tutorialbookingcontext);
        mod_tutorialbooking_tutorial::togglelock($tutorialid, false);
    }

    if ( $redirect ) {
        redirect($PAGE->url); // Come back to this script without additional param.
    }

} else {
    // Page setup stuff.
    $PAGE->set_title(get_string('pagetitle', 'tutorialbooking'));
    $PAGE->navbar->add(get_string('pagecrumb', 'tutorialbooking'));
    $PAGE->force_settings_menu(true);
    // Display the default page.
    $eventdata = array(
        'context' => $tutorialbookingcontext,
        'objectid' => $tutorial->id,
    );
    $event = course_module_edit_viewed::create($eventdata);
    $event->trigger();
    echo $output->header();
    echo $output->render(tutorialbooking::get($cm));
    echo $output->footer();
}
