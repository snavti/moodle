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
 * Prints a particular instance of hotquestion
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_hotquestion
 * @copyright 2011 Sun Zhigang
 * @copyright 2016 onwards AL Rachels (drachels@drachels.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use mod_hotquestion\local\results;
use \mod_hotquestion\event\course_module_viewed;

require_once("../../config.php");
require_once("lib.php");
require_once("locallib.php");
require_once("mod_form.php");
require_once($CFG->dirroot . '/comment/lib.php');
comment::init();

$id = required_param('id', PARAM_INT);                  // Course_module ID.
$ajax = optional_param('ajax', 0, PARAM_BOOL);          // Asychronous form request.
$action  = optional_param('action', '', PARAM_ACTION);  // Action(vote, newround).
$roundid = optional_param('round', -1, PARAM_INT);      // Round id.
$changegroup = optional_param('group', -1, PARAM_INT);  // Choose the current group.

if (! $cm = get_coursemodule_from_id('hotquestion', $id)) {
    throw new moodle_exception(get_string('incorrectmodule', 'hotquestion'));
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// Construct hotquestion instance.
$hq = new mod_hotquestion($id, $roundid);

// Confirm login.
require_login($hq->course, true, $hq->cm);

$context = context_module::instance($hq->cm->id);

$entriesmanager = has_capability('mod/hotquestion:manageentries', $context);
$canask = has_capability('mod/hotquestion:ask', $context);

if (!$entriesmanager && !$canask) {
    throw new moodle_exception(get_string('accessdenied', 'hotquestion'));
}

if (! $hotquestion = $DB->get_record("hotquestion", array("id" => $cm->instance))) {
    throw new moodle_exception(get_string('incorrectmodule', 'hotquestion'));
}

if (! $cw = $DB->get_record("course_sections", array("id" => $cm->section))) {
    throw new moodle_exception(get_string('incorrectmodule', 'hotquestion'));
}

// Trigger module viewed event.
if ($CFG->version > 2014051200) { // Moodle 2.7+.
    $params = array('objectid' => $hq->cm->id, 'context' => $context);
    $event = course_module_viewed::create($params);
    $event->trigger();
} else {
    add_to_log($hq->course->id, 'hotquestion', 'view', "view.php?id={$hq->cm->id}", $hq->instance->name, $hq->cm->id);
}

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Set page.
if (!$ajax) {
    $PAGE->set_url('/mod/hotquestion/view.php', array('id' => $hq->cm->id));
    $PAGE->set_title($hq->instance->name);
    $PAGE->set_heading($hq->course->shortname);
    $PAGE->set_context($context);
    $PAGE->set_cm($hq->cm);
    $PAGE->add_body_class('hotquestion');
}

require_capability('mod/hotquestion:view', $context);

// Get local renderer.
$output = $PAGE->get_renderer('mod_hotquestion');
$output->init($hq);

// Process submitted question.
if (has_capability('mod/hotquestion:ask', $context)) {
    $mform = new hotquestion_form(null, array($hq->instance->anonymouspost, $hq->cm));
    if ($fromform = $mform->get_data()) {
        // If there is a post, $fromform will contain text, format, id, and submitbutton.
        // 20210314 Prevent CSFR.
        confirm_sesskey();
        $timenow = time();

        // This will be overwritten after we have the entryid.
        $newentry = new stdClass();
        $newentry->hotquestion = $hq->instance->id;
        $newentry->content = $fromform->text_editor['text'];
        $newentry->format = $fromform->text_editor['format'];
        $newentry->userid = $USER->id;
        $newentry->time = $timenow;
        if ($fromform->anonymous = null) {
            $newentry->anonymous = $fromform->anonymous;
        } else {
            $newentry->anonymous = 0;
        }
        $newentry->approved = $hq->instance->approval;
        $newentry->tpriority = 0;
        $newentry->submitbutton = $fromform->submitbutton;

        if (!$hq->add_new_question($fromform)) { // Returns 1 if valid question submitted.
            redirect('view.php?id='.$hq->cm->id, get_string('invalidquestion', 'hotquestion'));
        }
        if (!$ajax) {
            redirect('view.php?id='.$hq->cm->id, get_string('questionsubmitted', 'hotquestion'));
        }
        die;
    }
}

// Handle priority, vote, newround, removeround, remove question, download questions, and approve question.
if (!empty($action)) {
    switch ($action) {
        case 'tpriority':
            if (has_capability('mod/hotquestion:manageentries', $context)) {
                $u = required_param('u',  PARAM_INT);  // Flag to change priority up or down.
                $q = required_param('q',  PARAM_INT);  // Question id to change priority.
                $hq->tpriority_change($u, $q);
                redirect('view.php?id='.$hq->cm->id, null); // Needed to prevent priority change on page reload.
            }
            break;
        case 'vote':
            if (has_capability('mod/hotquestion:vote', $context)) {
                $q = required_param('q',  PARAM_INT);  // Question id to vote.
                $hq->vote_on($q);
                redirect('view.php?id='.$hq->cm->id, null); // Needed to prevent heat toggle on page reload.
            }
            break;
        case 'removevote':
            if (has_capability('mod/hotquestion:vote', $context)) {
                $q = required_param('q',  PARAM_INT);  // Question id to vote.
                $hq->remove_vote($q);
                redirect('view.php?id='.$hq->cm->id, null); // Needed to prevent heat toggle on page reload.
            }
            break;
        case 'newround':
            if (has_capability('mod/hotquestion:manage', $context)) {
                $hq->add_new_round();
                // Added to make new empty round start without having to click the Reload icon.
                redirect('view.php?id='.$hq->cm->id, get_string('newroundsuccess', 'hotquestion'));
            }
            break;
        case 'removeround':
            if (has_capability('mod/hotquestion:manageentries', $context)) {
                $hq->remove_round();
                // Added to show round has been removed.
                redirect('view.php?id='.$hq->cm->id, get_string('removedround', 'hotquestion'));
            }
            break;
        case 'remove':
            if (has_capability('mod/hotquestion:manageentries', $context)) {
                $q = required_param('q',  PARAM_INT);  // Question id to remove.
                // Call remove_question function in locallib.
                $hq->remove_question($q);
                // Need redirect that goes to the round where removing question.
                redirect('view.php?id='.$hq->cm->id, get_string('questionremovesuccess', 'hotquestion'));
                // Does work without it as it just defaults to current round.
            }
            break;
        case 'download':
            if (has_capability('mod/hotquestion:manageentries', $context)) {
                $q = $cm->instance; // Course module to download questions from.
                // Call download question function in locallib.
                $hq->download_questions($q);
            }
            break;
        case 'approve':
            if (has_capability('mod/hotquestion:manageentries', $context)) {
                $q = required_param('q',  PARAM_INT);  // Question id to approve.
                // Call approve question function in locallib.
                $hq->approve_question($q);
                redirect('view.php?id='.$hq->cm->id, null); // Needed to prevent toggle on page reload.
            }
            break;
    }
}

// Start print page.
if (!$ajax) {
    // Added code to include the activity name, 10/05/16.
    $hotquestionname = format_string($hotquestion->name, true, array('context' => $context));
    echo $output->header();
    echo $OUTPUT->heading($hotquestionname);
    // Allow access at any time to manager and editing teacher but prevent access to students.
    if (!(has_capability('mod/hotquestion:manage', $context))) {
        // Check availability timeopen and timeclose. Added 10/2/16.
        if (!(results::hq_available($hotquestion))) {  // Availability restrictions.
            if ($hotquestion->timeclose != 0 && time() > $hotquestion->timeclose) {
                echo $output->hotquestion_inaccessible(get_string('hotquestionclosed',
                    'hotquestion', userdate($hotquestion->timeclose)));
            } else {
                echo $output->hotquestion_inaccessible(get_string('hotquestionopen',
                    'hotquestion', userdate($hotquestion->timeopen)));
            }
            echo $OUTPUT->footer();
            exit();
            // Password code can go here. e.g. // } else if {.
        }
    }
    // 20220301 Added activity completion to the hotquestion description.
    $cminfo = cm_info::create($cm);
    $completiondetails = \core_completion\cm_completion_details::get_instance($cminfo, $USER->id);
    $activitydates = \core\activity_dates::get_dates_for_module($cminfo, $USER->id);
    echo $output->introduction($cminfo, $completiondetails, $activitydates);

    // 20211219 Added link to all HotQuestion activities.
    echo '<span style="float:right"><a href="index.php?id='
         .$course->id
         .'">'
         .get_string('viewallhotquestions', 'hotquestion')
         .'</a></span><br>';
    echo

    // Print group information (A drop down box will be displayed if the user
    // is a member of more than one group, or has access to all groups).
    groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/hotquestion/view.php?id='.$cm->id);

    // Print the textarea box for typing submissions in.
    if (has_capability('mod/hotquestion:ask', $context)) {
        $mform->display();
    }
}

echo $output->container_start(null, 'questions_list');
// Print toolbar.
echo $output->container_start("toolbar");
echo $output->toolbar(has_capability('mod/hotquestion:manageentries', $context));
echo $output->container_end();

// Print questions list from the current round.
echo $output->questions(has_capability('mod/hotquestion:vote', $context));
echo $output->container_end();

// Finish the page.
if (!$ajax) {
    echo $output->footer();
}
