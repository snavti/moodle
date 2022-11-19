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
 * Renderer for the student page.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_tutorialbooking\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderer for the student page.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_renderer extends \plugin_renderer_base {
    /**
     * Display a confirmation prompt for the user to remove their signup.
     *
     * @param stdClass $cm A course module record for a tutorialbooking activity.
     */
    public function delete_signup_confirm($cm) {
        $cancelaction = new \moodle_url('/mod/tutorialbooking/view.php', array('id' => $cm->id, 'redirect' => 0));
        $confirmparams = array(
            'id' => $cm->id,
            'action' => 'confirmedremove',
            'redirect' => 0,
            'sesskey' => sesskey(),
        );
        $confirmaction = new \moodle_url('/mod/tutorialbooking/view.php', $confirmparams);
        echo $this->header();
        echo $this->confirm(get_string('confirmremovefromslot', 'mod_tutorialbooking'), $confirmaction, $cancelaction);
        echo $this->footer();
    }

    /**
     * Renders a signup for students.
     *
     * @param \mod_tutorialbooking\output\signup $signup
     * @return bool|string
     */
    public function render_signup(signup $signup) {
        $data = $signup->export_for_template($this);
        return $this->render_from_template('mod_tutorialbooking/student_signup', $data);
    }

    /**
     * Renders a slot for a students.
     *
     * @param \mod_tutorialbooking\output\slot $slot
     * @return bool|string
     */
    public function render_slot(slot $slot) {
        $data = $slot->export_for_template($this);
        return $this->render_from_template('mod_tutorialbooking/student_slot', $data);
    }

    /**
     * Renders a tutorial booking activity for students.
     *
     * @param \mod_tutorialbooking\output\tutorialbooking $tutorialbooking
     * @return bool|string
     */
    public function render_tutorialbooking(tutorialbooking $tutorialbooking) {
        $data = $tutorialbooking->export_for_template($this);
        return $this->render_from_template('mod_tutorialbooking/student_tutorialbooking', $data);
    }
}
