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
 * Library of interface functions and constants for module hotquestion.
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the hotquestion specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_hotquestion
 * @copyright 2011 Sun Zhigang
 * @copyright 2016 onwards AL Rachels drachels@drachels.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die(); // @codingStandardsIgnoreLine
use mod_hotquestion\local\results;

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $hotquestion An object from the form in mod_form.php
 * @return int The id of the newly inserted hotquestion record
 */
function hotquestion_add_instance($hotquestion) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/hotquestion/locallib.php');

    $hotquestion->timecreated = time();
    // Fixed instance error 02/15/19.
    $hotquestion->id = $DB->insert_record('hotquestion', $hotquestion);

    // You may have to add extra stuff in here.
    // Added next line for behat test 2/11/19.
    $cmid = $hotquestion->coursemodule;

    results::hotquestion_update_calendar($hotquestion, $cmid);

    return $hotquestion->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $hotquestion An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function hotquestion_update_instance($hotquestion) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/hotquestion/locallib.php');

    if (empty($hotquestion->timeopen)) {
        $hotquestion->timeopen = 0;
    }
    if (empty($hotquestion->timeclose)) {
        $hotquestion->timeclose = 0;
    }

    $cmid       = $hotquestion->coursemodule;
    $cmidnumber = $hotquestion->cmidnumber;
    $courseid   = $hotquestion->course;

    $hotquestion->id = $hotquestion->instance;

    $context = context_module::instance($cmid);

    $hotquestion->timemodified = time();
    $hotquestion->id = $hotquestion->instance;

    // You may have to add extra stuff in here.
    results::hotquestion_update_calendar($hotquestion, $cmid);

    return $DB->update_record('hotquestion', $hotquestion);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function hotquestion_delete_instance($id) {
    global $DB;

    if (! $hotquestion = $DB->get_record('hotquestion', array('id' => $id))) {
        return false;
    }

    if (! reset_instance($hotquestion->id)) {
        return false;
    }

    if (! $DB->delete_records('hotquestion', array('id' => $hotquestion->id))) {
        return false;
    }

    return true;
}

/**
 * Clear all questions and votes.
 *
 * @param int $hotquestionid
 * @return boolean Success/Failure
 */
function reset_instance($hotquestionid) {
    global $DB;

    $questions = $DB->get_records('hotquestion_questions', array('hotquestion' => $hotquestionid));
    foreach ($questions as $question) {
        if (! $DB->delete_records('hotquestion_votes', array('question' => $question->id))) {
            return false;
        }
    }

    if (! $DB->delete_records('hotquestion_questions', array('hotquestion' => $hotquestionid))) {
        return false;
    }

    if (! $DB->delete_records('hotquestion_rounds', array('hotquestion' => $hotquestionid))) {
        return false;
    }

    return true;
}

/**
 * Get all questions into an array for export as csv file.
 *
 * @param int $hotquestionid
 * @return boolean Success/Failure
 */
function get_question_list($hotquestionid) {
    global $CFG, $USER, $DB;
    $params = array();
    $toreturn = array();
    $questionstblname = $CFG->prefix."hotquestion_questions";
    $userstblname = $CFG->prefix."user";
    $sql = 'SELECT COUNT(*) FROM {hotquestion_questions} WHERE userid>0';
    return $DB->get_records_sql($sql, array($USER->id));
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * @param int $course
 * @param int $user
 * @param int $mod
 * @param int $hotquestion
 * $return->time = the time they did it
 * $return->info = a short text description
 * @return null
 * @todo Finish documenting this function
 */
function hotquestion_user_outline($course, $user, $mod, $hotquestion) {
    $return = new stdClass;
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 * @param int $course
 * @param int $user
 * @param int $mod
 * @param int $hotquestion
 * @return boolean
 * @todo Finish documenting this function
 */
function hotquestion_user_complete($course, $user, $mod, $hotquestion) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in hotquestion activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @param stdClass $course
 * @param bool $viewfullnames
 * @param int $timestart
 * @return boolean
 */
function hotquestion_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $USER, $DB, $OUTPUT;

    $dbparams = array($timestart, $course->id, 'hotquestion');

    if ($CFG->branch > 30) { // If Moodle less than version 3.1 skip this.
        $userfieldsapi = \core_user\fields::for_userpic();
        $namefields = $userfieldsapi->get_sql('u', false, '', 'duserid', false)->selects;
    } else {
        $namefields = user_picture::fields('u', null, 'userid');
    }
    $sql = "SELECT hqq.id, hqq.time, cm.id AS cmid, $namefields
         FROM {hotquestion_questions} hqq
              JOIN {hotquestion} hq         ON hq.id = hqq.hotquestion
              JOIN {course_modules} cm ON cm.instance = hq.id
              JOIN {modules} md        ON md.id = cm.module
              JOIN {user} u            ON u.id = hqq.userid
         WHERE hqq.time > ? AND
               hq.course = ? AND
               md.name = ?
         ORDER BY hqq.time ASC
    ";

    $newentries = $DB->get_records_sql($sql, $dbparams);

    $modinfo = get_fast_modinfo($course);
    $show    = array();
    $grader  = array();
    $showrecententries = get_config('hotquestion', 'showrecentactivity');

    foreach ($newentries as $anentry) {

        if (!array_key_exists($anentry->cmid, $modinfo->get_cms())) {
            continue;
        }
        $cm = $modinfo->get_cm($anentry->cmid);

        if (!$cm->uservisible) {
            continue;
        }
        if ($anentry->userid == $USER->id) {
            $show[] = $anentry;
            continue;
        }
        $context = context_module::instance($anentry->cmid);

        // The act of submitting of entries may be considered private -
        // only graders will see it if specified.
        if (empty($showrecententries)) {
            if (!array_key_exists($cm->id, $grader)) {
                $grader[$cm->id] = has_capability('moodle/grade:viewall', $context);
            }
            if (!$grader[$cm->id]) {
                continue;
            }
        }

        $groupmode = groups_get_activity_groupmode($cm, $course);

        if ($groupmode == SEPARATEGROUPS &&
                !has_capability('moodle/site:accessallgroups',  $context)) {
            if (isguestuser()) {
                // Shortcut - guest user does not belong into any group.
                continue;
            }

            // This will be slow - show only users that share group with me in this cm.
            if (!$modinfo->get_groups($cm->groupingid)) {
                continue;
            }
            $usersgroups = groups_get_all_groups($course->id, $anentry->userid, $cm->groupingid);
            if (is_array($usersgroups)) {
                $usersgroups = array_keys($usersgroups);
                $intersect = array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid));
                if (empty($intersect)) {
                    continue;
                }
            }
        }
        $show[] = $anentry;
    }

    if (empty($show)) {
        return false;
    }

    echo $OUTPUT->heading(get_string('modulenameplural', 'hotquestion').':', 3);

    foreach ($show as $submission) {
        $cm = $modinfo->get_cm($submission->cmid);
        $context = context_module::instance($submission->cmid);
        $link = $CFG->wwwroot.'/mod/hotquestion/view.php?id='.$cm->id;
        $name = $cm->name;
        print_recent_activity_note($submission->time,
                                   $submission,
                                   $name,
                                   $link,
                                   false,
                                   $viewfullnames);
    }
    return true;
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function hotquestion_cron () {
    return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of hotquestion. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 * @param int $hotquestionid
 * @return boolean|array false if no participants, array of objects otherwise
 */
function hotquestion_get_participants($hotquestionid) {
    return false;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all posts from the specified forum
 * and clean up any related data.
 *
 * @param stdClass $data
 * @return array
 */
function hotquestion_reset_userdata($data) {
    global $DB;

    $status = array();
    if (!empty($data->reset_hotquestion)) {
        $instances = $DB->get_records('hotquestion', array('course' => $data->courseid));
        foreach ($instances as $instance) {
            if (reset_instance($instance->id)) {
                $status[] = array('component' => get_string('modulenameplural', 'hotquestion')
                , 'item' => get_string('resethotquestion', 'hotquestion')
                .': '.$instance->name, 'error' => false);
            }
        }
    }

    return $status;
}

/**
 * Called by course/reset.php
 *
 * @param stdClass $mform
 */
function hotquestion_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'hotquestionheader', get_string('modulenameplural', 'hotquestion'));
    $mform->addElement('checkbox', 'reset_hotquestion', get_string('resethotquestion', 'hotquestion'));
}

/**
 * Indicates API features that the hotquestion supports.
 *
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_COMPLETION_HAS_RULES
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @uses FEATURE_RATE
 * @uses FEATURE_SHOW_DESCRIPTION
 * @uses FEATURE_BACKUP_MOODLE2
 * @uses FEATURE_COMMENT
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function hotquestion_supports($feature) {
    global $CFG;
    if ($CFG->branch > 311) {
        switch($feature) {
            case FEATURE_MOD_PURPOSE:
                return MOD_PURPOSE_COLLABORATION;
            case FEATURE_BACKUP_MOODLE2:
                return true;
            case FEATURE_COMMENT:
                return true;
            case FEATURE_COMPLETION_HAS_RULES:
                return false;
            case FEATURE_COMPLETION_TRACKS_VIEWS:
                return true;
            case FEATURE_GRADE_HAS_GRADE:
                return false;
            case FEATURE_GRADE_OUTCOMES:
                return false;
            case FEATURE_GROUPS:
                return true;
            case FEATURE_GROUPINGS:
                return true;
            case FEATURE_GROUPMEMBERSONLY:
                return true;
            case FEATURE_MOD_INTRO:
                return true;
            case FEATURE_RATE:
                return false;
            case FEATURE_SHOW_DESCRIPTION:
                return true;

            default:
                return null;
        }
    } else {
        switch($feature) {
            case FEATURE_BACKUP_MOODLE2:
                return true;
            case FEATURE_COMMENT:
                return true;
            case FEATURE_COMPLETION_HAS_RULES:
                return false;
            case FEATURE_COMPLETION_TRACKS_VIEWS:
                return true;
            case FEATURE_GRADE_HAS_GRADE:
                return false;
            case FEATURE_GRADE_OUTCOMES:
                return false;
            case FEATURE_GROUPS:
                return true;
            case FEATURE_GROUPINGS:
                return true;
            case FEATURE_GROUPMEMBERSONLY:
                return true;
            case FEATURE_MOD_INTRO:
                return true;
            case FEATURE_RATE:
                return false;
            case FEATURE_SHOW_DESCRIPTION:
                return true;

            default:
                return null;
        }
    }
}
    /**
     * Validate comment parameter before perform other comments actions.
     *
     * @param stdClass $commentparam {
     *              context  => context the context object
     *              courseid => int course id
     *              cm       => stdClass course module object
     *              commentarea => string comment area
     *              itemid      => int itemid
     * }
     * @return boolean
     */
function hotquestion_comment_validate($commentparam) {
    global $DB;
    $debug['lib.php Tracking hotquestion_comment_validate enter: '] = 'Made it to the validation function in HQ lib.php file!';
    $debug['lib.php Tracking hotquestion_comment_validate $commentparam: '] = $commentparam;
    // Validate comment area.
    if ($commentparam->commentarea != 'hotquestion_questions') {
        throw new comment_exception('invalidcommentarea');
    }
    if (!$record = $DB->get_record('hotquestion_questions', array('id' => $commentparam->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if (!$hotquestion = $DB->get_record('hotquestion', array('id' => $record->hotquestion))) {
        throw new comment_exception('invalidid', 'data');
    }
    if (!$course = $DB->get_record('course', array('id' => $hotquestion->course))) {
        throw new comment_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('hotquestion', $hotquestion->id, $course->id)) {
        throw new comment_exception('invalidcoursemodule');
    }
    $context = context_module::instance($cm->id);

    if ($hotquestion->approval and !$record->approved and !has_capability('mod/hotquestion:manageentries', $context)) {
        throw new comment_exception('notapproved', 'hotquestion');
    }
    // Validate context id.
    if ($context->id != $commentparam->context->id) {
        throw new comment_exception('invalidcontext');
    }
    // Validation for comment deletion.
    if (!empty($commentparam->commentid)) {
        if ($comment = $DB->get_record('comments', array('id' => $commentparam->commentid))) {
            if ($comment->commentarea != 'hotquestion_questions') {
                throw new comment_exception('invalidcommentarea');
            }
            if ($comment->contextid != $commentparam->context->id) {
                throw new comment_exception('invalidcontext');
            }
            if ($comment->itemid != $commentparam->itemid) {
                throw new comment_exception('invalidcommentitemid');
            }
        } else {
            throw new comment_exception('invalidcommentid');
        }
    }
    return true;
}

/**
 * Running addtional permission check on plugin, for example, plugins
 * may have switch to turn on/off comments option, this callback will
 * affect UI display, not like pluginname_comment_validate only throw
 * exceptions.
 * Capability check has been done in comment->check_permissions(), we
 * don't need to do it again here.
 *
 * @param stdClass $commentparam {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return array
 */
function hotquestion_comment_permissions($commentparam) {
    return array('post' => true, 'view' => true);
}

/**
 * Returns all other caps used in module.
 * @return array
 */
function hotquestion_get_extra_capabilities() {
    return array('moodle/comment:post',
                 'moodle/comment:view',
                 'moodle/site:viewfullnames',
                 'moodle/site:trustcontent',
                 'moodle/site:accessallgroups');
}
