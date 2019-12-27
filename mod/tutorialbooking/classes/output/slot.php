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
 * Renderable for tutorial slots.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_tutorialbooking\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderable for a tutorial slot.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slot implements \renderable, \templatable {
    /** @var int The database id of the tutorial slot. */
    public $id;

    /** @var array The list of users who have signed up. */
    public $signups = [];

    /** @var int The number of spaces in the tutorial slot. */
    public $spaces;

    /** @var string The html summary of the tutorial slot. */
    public $summary;

    /** @var string The name of the tutorial slot. */
    public $title;

    /** @var \mod_tutorialbooking\output\tutorialbooking The tutorial this slot belongs to. */
    public $tutorial;

    /** @var int The position of the slot in the tutorial booking. */
    public $sequence;

    /**
     * Creates a tutorial slot from a database record from the tutorialbooking_sessions table.
     *
     * @param \stdClass $record
     * @param \mod_tutorialbooking\output\tutorialbooking $tutorial
     * @param bool $external If true the text will be formatted for external tools.
     * @return \mod_tutorialbooking\output\slot
     */
    public static function create_from_record(\stdClass $record, tutorialbooking $tutorial, bool $external = false) : slot {
        $renderable = new slot();
        $renderable->id = $record->id;
        $renderable->spaces = $record->spaces;
        if ($external) {
            list($renderable->title, $titelformat) =
                external_format_text($record->description, $record->descformat, $tutorial->context->id);
            list($renderable->summary, $summaryformat) =
                external_format_text($record->summary, $record->summaryformat, $tutorial->context->id);
        } else {
            $renderable->title = format_text($record->description, $record->descformat, ['context' => $tutorial->context]);
            $renderable->summary = format_text($record->summary, $record->summaryformat, ['context' => $tutorial->context]);
        }
        $renderable->sequence = $record->sequence;
        $renderable->tutorial = $tutorial;
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
        $viewparams = ['id' => $this->tutorial->cm->id, 'redirect' => 0];
        $viewpage = new \moodle_url('/mod/tutorialbooking/view.php', $viewparams);
        $adminparams = ['tutorialid' => $this->tutorial->id, 'courseid' => $this->tutorial->course, 'id' => $this->id];
        $adminpage = new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', $adminparams);
        $registerparams = ['courseid' => $this->tutorial->course, 'sessionid' => $this->id];
        $registerpage = new \moodle_url('/mod/tutorialbooking/tutorial_register.php', $registerparams);
        // Check if the user can signup other users to the slot.
        $signupothers = $this->tutorial->canaddstudent;
        $oversubscribe = $this->tutorial->canoversubscribe;
        $usedspaces = count($this->signups);
        $full = $usedspaces >= $this->spaces;
        $freespaces = abs($this->spaces - count($this->signups));
        // Students can signup only if thy are not already signed up and they have the correct capability.
        $cansignup =  !isset($this->tutorial->mysignup) && $this->tutorial->cansignup;
        $slot = (object) array(
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'spaces' => $this->spaces,
            'signups' => [],
            'usedspaces' => $usedspaces ,
            'freespaces' => $freespaces,
            'oversubscribed' => ($usedspaces > $this->spaces),
            'signupothers' => $signupothers && (!$full || $oversubscribe),
            'signedup' => ($this->id == $this->tutorial->mysignup),
            'cansignup' => $cansignup && !$full,
            'urlsignup' => new \moodle_url($viewpage, ['action' => 'signup', 'sessionid' => $this->id]),
            'urlremove' => new \moodle_url($viewpage, ['action' => 'remove', 'sessionid' => $this->id]),
            'urledit' => new \moodle_url($adminpage, ['action' => 'edit']),
            'urldelete' => new \moodle_url($adminpage, ['action' => 'deletesession']),
            'urladdusers' => new \moodle_url($adminpage, ['action' => 'addusers']),
            'urlregistername' => $registerpage,
            'urlregisterdate' => new \moodle_url($registerpage, ['format' => \mod_tutorialbooking_register::ORDER_DATE]),
            'urlmessage' => new \moodle_url($adminpage, ['action' => 'emailgroup']),
            'urlmoveup' => new \moodle_url($adminpage, ['action' => 'moveup', 'currentpos' => $this->sequence]),
            'urlmovedown' => new \moodle_url($adminpage, ['action' => 'movedown', 'currentpos' => $this->sequence]),
        );
        $privatesignups = $this->tutorial->privacy == \mod_tutorialbooking_tutorial::PRIVACY_SHOWOWN;
        $teacher = $this->tutorial->viewadmin;
        // Only send back signups the user should see.
        foreach ($this->signups as $signup) {
            if (!$privatesignups || $teacher) {
                // Signups are not private, or the user is a teacher.
                $slot->signups[] = $output->render($signup);
            } else if ($privatesignups && $signup->isme) {
                // Private signups and it is the users signup.
                $slot->signups[] = get_string('yousignedup', 'tutorialbooking');
            }
        }
        return $slot;
    }
}
