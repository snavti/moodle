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

namespace mod_tutorialbooking\privacy;

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\approved_userlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\transform;
use \core_privacy\local\request\userlist;
use \core_privacy\local\request\helper;

defined('MOODLE_INTERNAL') || die;

/**
 * Definition of the data that is stored by the plugin.
 *
 * @see https://docs.moodle.org/dev/Privacy_API
 *
 * @package    mod_tutorialbooking
 * @category   privacy
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {
    /**
     * Returns meta data about the Tutorial booking activity.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        // Describes the core Moodle sub-systems that the plugin uses.
        $collection->add_subsystem_link('core_message', [], 'privacy:metadata:core_message');
        // Describes the personal information in the database tables.
        $signups = [
            'userid' => 'privacy:metadata:tutorialbooking_signups:userid',
            'sessionid' => 'privacy:metadata:tutorialbooking_signups:sessionid',
            'signupdate' => 'privacy:metadata:tutorialbooking_signups:signupdate',
        ];
        $messages = [
            'sentby' => 'privacy:metadata:tutorialbooking_messages:sentby',
            'senttime' => 'privacy:metadata:tutorialbooking_messages:senttime',
            'subject' => 'privacy:metadata:tutorialbooking_messages:subject',
            'sentto' => 'privacy:metadata:tutorialbooking_messages:sentto',
            'message' => 'privacy:metadata:tutorialbooking_messages:message',
        ];
        $collection->add_database_table('tutorialbooking_signups', $signups, 'privacy:metadata:tutorialbooking_signups');
        $collection->add_database_table('tutorialbooking_messages', $messages, 'privacy:metadata:tutorialbooking_messages');
        // No user preferences are stored.
        // No data is exported to any external systems.
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {tutorialbooking} t ON t.id = cm.instance
            INNER JOIN {tutorialbooking_signups} s ON s.tutorialid = t.id
                 WHERE s.userid = :userid";
        $params = array(
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'tutorialbooking',
            'userid' => $userid,
        );
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        $sql2 = "SELECT c.id
                   FROM {context} c
             INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
             INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
             INNER JOIN {tutorialbooking} t ON t.id = cm.instance
             INNER JOIN {tutorialbooking_messages} ms ON ms.tutorialbookingid = t.id
                  WHERE ms.sentby = :userid";
        $contextlist->add_from_sql($sql2, $params);
        $contextlist->set_component('mod_tutorialbooking');
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        // Build up the data about the tutorials.
        $tutorialsql = "SELECT t.id, c.id AS ctxid
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {tutorialbooking} t ON t.id = cm.instance
                 WHERE c.id $contextsql";
        $tutorialparams = array(
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'tutorialbooking',
        );
        $tutorialparams += $contextparams;
        $tutorials = $DB->get_recordset_sql($tutorialsql, $tutorialparams);

        foreach ($tutorials as $tutorial) {
            $context = \context::instance_by_id($tutorial->ctxid);
            $data = helper::get_context_data($context, $user);
            writer::with_context($context)
                ->export_data([], $data);
            helper::export_context_files($context, $user);
        }

        static::export_signups($user, $contextlist);
        static::export_messages($user, $contextlist);
    }

    /**
     * Exports the messages a user has sent through the tutorial booking plugin.
     *
     * @param \stdClass $user
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     */
    protected static function export_messages($user, approved_contextlist $contextlist) {
        global $DB;
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $messagesql = "SELECT ms.*, c.id AS ctxid
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {tutorialbooking} t ON t.id = cm.instance
            INNER JOIN {tutorialbooking_messages} ms ON ms.tutorialbookingid = t.id AND ms.sentby = :userid
                 WHERE c.id $contextsql";
        $messageparams = array(
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'tutorialbooking',
            'userid' => $user->id,
        );
        $messageparams += $contextparams;
        $messages = $DB->get_recordset_sql($messagesql, $messageparams);

        $subcontext = get_string('privacy:export:messages', 'mod_tutorialbooking');
        foreach ($messages as $message) {
            $context = \context::instance_by_id($message->ctxid);
            $messagecontext = "$message->id - $message->subject";
            $messagedata = (object) array(
                'subject' => format_string($message->subject),
                'message' => format_text($message->message, FORMAT_HTML),
                'created' => transform::datetime($message->senttime),
                'sent_by_you' => transform::yesno($message->sentby == $user),
            );
            writer::with_context($context)->export_data([$subcontext, $messagecontext], $messagedata);
        }
    }

    /**
     * Exports the signups a user has on Tutorial bookings.
     *
     * @param \stdClass $user
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     */
    protected static function export_signups($user, approved_contextlist $contextlist) {
        global $DB;
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        // Export the signups.
        $signupsql = "SELECT s.id, c.id AS ctxid, ts.description, ts.descformat, ts.summary, ts.summaryformat, s.signupdate
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {tutorialbooking} t ON t.id = cm.instance
            INNER JOIN {tutorialbooking_sessions} ts ON ts.tutorialid = t.id
            INNER JOIN {tutorialbooking_signups} s ON s.tutorialid = t.id AND ts.id = s.sessionid AND s.userid = :userid
                 WHERE c.id $contextsql";
        $signupparams = array(
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'tutorialbooking',
            'userid' => $user->id,
        );
        $signupparams += $contextparams;
        $signups = $DB->get_recordset_sql($signupsql, $signupparams);

        $signupsubcontext = array(
            get_string('privacy:export:signups', 'mod_tutorialbooking'),
        );
        foreach ($signups as $signup) {
            $context = \context::instance_by_id($signup->ctxid);
            $signupdata = (object) array(
                'signup_date' => transform::datetime($signup->signupdate),
            );
            $signupdata->slot = format_text($signup->description, $signup->descformat, (object) [
                'context' => $context,
            ]);
            $signupdata->summary = format_text($signup->summary, $signup->summaryformat, (object) [
                'context' => $context,
            ]);
            writer::with_context($context)->export_data($signupsubcontext, $signupdata);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        // Check that this is a context_module.
        if (!$context instanceof \context_module) {
            return;
        }

        // Get the course module.
        if (!$cm = get_coursemodule_from_id('tutorialbooking', $context->instanceid)) {
            return;
        }

        $tutorialid = $cm->instance;
        $DB->delete_records('tutorialbooking_signups', ['tutorialid' => $tutorialid]);
        $DB->delete_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorialid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        $userid = $user->id;
        foreach ($contextlist as $context) {
            // Check if this is the correct type of context.
            if (!$context instanceof \context_module) {
                continue;
            }
            // Make sure it is a tutorial booking context.
            if (!$cm = get_coursemodule_from_id('tutorialbooking', $context->instanceid)) {
                continue;
            }
            $tutorialid = $cm->instance;
            $DB->delete_records('tutorialbooking_signups', ['tutorialid' => $tutorialid, 'userid' => $userid]);
            $DB->delete_records('tutorialbooking_messages', ['tutorialbookingid' => $tutorialid, 'sentby' => $userid]);
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $params = array(
            'modname' => 'tutorialbooking',
            'contextid' => $context->id,
            'contextlevel' => CONTEXT_MODULE,
        );

        $sql1 = "SELECT s.userid
                   FROM {context} c
             INNER JOIN {course_modules} cm ON cm.id = c.instanceid
             INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
             INNER JOIN {tutorialbooking} t ON t.id = cm.instance
             INNER JOIN {tutorialbooking_signups} s ON s.tutorialid = t.id
                  WHERE c.id = :contextid AND c.contextlevel = :contextlevel";
        $userlist->add_from_sql('userid', $sql1, $params);

        $sql2 = "SELECT ms.sentby
                   FROM {context} c
             INNER JOIN {course_modules} cm ON cm.id = c.instanceid
             INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
             INNER JOIN {tutorialbooking} t ON t.id = cm.instance
             INNER JOIN {tutorialbooking_messages} ms ON ms.tutorialbookingid = t.id
                  WHERE c.id = :contextid AND c.contextlevel = :contextlevel";
        $userlist->add_from_sql('sentby', $sql2, $params);
    }

    /**
     * Delete data for multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }
        // Make sure it is a tutorial booking context.
        if (!$cm = get_coursemodule_from_id('tutorialbooking', $context->instanceid)) {
            return;
        }

        list($sql, $params) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params['tutorial'] = $cm->instance;
        $DB->delete_records_select('tutorialbooking_signups', "tutorialid = :tutorial AND userid $sql", $params);
        $DB->delete_records_select('tutorialbooking_messages', "tutorialbookingid = :tutorial AND sentby $sql", $params);
    }
}
