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
 * Class for exporting data from a tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tutorialbooking;

defined('MOODLE_INTERNAL') || die;

/**
 * Class for exporting data from a tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class export {
    /** @var bool Flag for formatting data for the Business School. */
    public static $uonbs = false;

    /**
     * Formats a record returned by the data get methods.
     *
     * @param \stdClass $record
     * @return \stdClass
     */
    public static function format_record($record) {
        $userfields = get_all_user_name_fields();
        $record->sessiontitle = strip_tags($record->sessiontitle);
        if (self::$uonbs) {
            $record->realname = "{$record->lastname}, {$record->firstname}";
        } else {
            $record->realname = fullname($record);
        }
        // Remove the id.
        unset($record->id);
        // Remove the fields used to build the user's fullname.
        foreach ($userfields as $field) {
            unset($record->$field);
        }
        return (array) $record;
    }

    /**
     * Get a list of people that have signed upto tutorial slots.
     *
     * @param int $tutorialid The tutorial id.
     * @return \moodle_recordset
     */
    public static function getexport($tutorialid) {
        global $DB;
        $userdetails = get_all_user_name_fields(true, 'u');
        $sesfields = "ses.description AS sessiontitle";
        $userfields = "u.idnumber, u.username, NULL AS realname, $userdetails";
        $coursefields = "c.fullname AS coursefullname";
        $sql = "SELECT sup.id, $sesfields, $userfields, $coursefields
                  FROM {course} c, {tutorialbooking} t, {tutorialbooking_sessions} ses, {tutorialbooking_signups} sup, {user} u
                 WHERE ses.tutorialid = t.id
                   AND sup.sessionid = ses.id
                   AND sup.userid = u.id
                   AND sup.tutorialid = :tutorialid
                   AND c.id = t.course";

        return $DB->get_recordset_sql($sql, array('tutorialid' => $tutorialid));
    }

    /**
     * Get a list of all tutorial slot signups on a course.
     *
     * @param int $courseid The course id.
     * @return \moodle_recordset
     */
    public static function getexportcourse($courseid) {
        global $DB;
        $userdetails = get_all_user_name_fields(true, 'u');
        $sheetfields = "t.name AS signupsheet";
        $sesfields = "ses.description AS sessiontitle";
        $userfields = "u.idnumber, u.username, NULL AS realname, $userdetails";
        $coursefields = "c.fullname AS coursefullname";
        $sql = "SELECT sup.id, $sheetfields, $sesfields, $userfields, $coursefields
                  FROM {course} c, {tutorialbooking} t, {tutorialbooking_sessions} ses, {tutorialbooking_signups} sup, {user} u
                 WHERE ses.tutorialid = t.id
                   AND sup.sessionid = ses.id
                   AND sup.userid = u.id
                   AND sup.courseid = :courseid
                   AND c.id = t.course";

        return $DB->get_recordset_sql($sql, array('courseid' => $courseid));
    }
}
