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
 * Renderable for a sign up.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_tutorialbooking\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderable for a sign up.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signup implements \renderable, \templatable {
    /** @var int The database id of the tutorial sign up. */
    public $id;

    /** @var int The id of the user who has signed up. */
    public $userid;

    /** @var string The display name of the the user. */
    public $name;

    /** @var int Timestamp of when the sign up was created. */
    public $date;

    /** @var bool Stores if this is the signup for the current user. */
    public $isme = false;

    /** @var \mod_tutorialbooking\output\slot The tutorial booking slot this signup is for. */
    public $slot;

    /**
     * Creates a signup from a database record.
     *
     * @param \stdClass $record
     * @param \mod_tutorialbooking\output\slot $slot
     * @return \mod_tutorialbooking\output\signup
     */
    public static function create_from_record(\stdClass $record, slot $slot) : signup {
        global $USER;
        $renderable = new signup();
        $renderable->id = $record->id;
        $renderable->userid = $record->userid;
        $renderable->date = $record->signupdate;
        if ($USER->id == $record->userid) {
            $renderable->isme = true;
        }
        $renderable->name = fullname($record);
        $renderable->slot = $slot;
        return $renderable;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param \renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return \stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        $teacherparams = array(
            'tutorialid' => $this->slot->tutorial->id,
            'courseid' => $this->slot->tutorial->course,
            'id' => $this->slot->id
        );
        $teacherurl = new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', $teacherparams);
        $signup = (object) array(
            'id' => $this->id,
            'userid' => $this->userid,
            'name' => $this->name,
            'isme' => $this->isme,
            'date' => $this->date,
            'canremoveusers' => $this->slot->tutorial->canremoveusers,
            'urldelete' => new \moodle_url($teacherurl, ['action' => 'removesignup', 'user' => $this->userid]),
        );
        return $signup;
    }
}
