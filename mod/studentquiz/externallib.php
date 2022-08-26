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
 * Defines external functions for the studentquiz module.
 *
 * @package mod_studentquiz
 * @author Huong Nguyen <huongnv13@gmail.com>
 * @copyright 2019 HSR (http://www.hsr.ch)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_studentquiz\local\studentquiz_helper;
use mod_studentquiz\utils;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/studentquiz/locallib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/questionlib.php');

/**
 * Defines external functions for the studentquiz module.
 *
 * @package mod_studentquiz
 * @author Huong Nguyen <huongnv13@gmail.com>
 * @copyright 2019 HSR (http://www.hsr.ch)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_studentquiz_external extends external_api {

    /**
     * Get the required question state parameters.
     * @return external_function_parameters
     */
    public static function change_question_state_parameters() {
        return new external_function_parameters([
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'cmid' => new external_value(PARAM_INT, 'CM id', VALUE_REQUIRED),
                'questionid' => new external_value(PARAM_INT, 'Question id', VALUE_REQUIRED),
                'state' => new external_value(PARAM_INT, 'Question state', VALUE_REQUIRED)
        ]);
    }

    /**
     * Set the question state as provided.
     *
     * @param int $courseid Course id
     * @param int $cmid Course module id
     * @param int $questionid Question id
     * @param int $state State value
     * @return array Response
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function change_question_state($courseid, $cmid, $questionid, $state) {
        global $PAGE, $USER;

        if ($state == studentquiz_helper::STATE_HIDE) {
            $type = 'hidden';
            $value = 1;
        } else if ($state == studentquiz_helper::STATE_DELETE) {
            $type = 'deleted';
            $value = 1;
        } else {
            $type = 'state';
            $value = $state;
        }

        // Student can not delete the question when the question is in approved state.
        $context = \context_course::instance($courseid);
        $canmanage = has_capability('mod/studentquiz:manage', $context);
        if (!$canmanage && $state == studentquiz_helper::STATE_DELETE) {
            if (utils::get_state_question($questionid) == studentquiz_helper::STATE_APPROVED) {
                $result = [];
                $result['status'] = get_string('api_state_change_error_title', 'studentquiz');
                $result['message'] = get_string('api_state_change_error_content', 'studentquiz');
                return $result;
            }
        }

        mod_studentquiz_change_state_visibility($questionid, $type, $value);
        utils::question_save_action($questionid, $USER->id, $state);

        // Additionally always unhide the question when it got approved.
        if ($state == studentquiz_helper::STATE_APPROVED && utils::check_is_question_hidden($questionid)) {
            mod_studentquiz_change_state_visibility($questionid, 'hidden', 0);
            utils::question_save_action($questionid, null, studentquiz_helper::STATE_SHOW);
        }

        $course = get_course($courseid);
        $cm = get_coursemodule_from_id('studentquiz', $cmid);
        $context = context_module::instance($cmid);
        $PAGE->set_context($context);
        if (!$canmanage) {
            if ($state == studentquiz_helper::STATE_REVIEWABLE) {
                mod_studentquiz_notify_reviewable_question($questionid, $course, $cm);
            }
        } else {
            mod_studentquiz_state_notify($questionid, $course, $cm, $type);
        }
        $result = [];
        $result['status'] = get_string('api_state_change_success_title', 'studentquiz');
        $result['message'] = get_string('api_state_change_success_content', 'studentquiz');
        return $result;
    }

    /**
     * Get available state return fields.
     * @return external_single_structure
     */
    public static function change_question_state_returns() {
        return new external_single_structure([
                'status' => new external_value(PARAM_TEXT, 'status'),
                'message' => new external_value(PARAM_TEXT, 'message')
        ]);
    }
}
