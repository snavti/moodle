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
 * Used by the tutorial booking renderer to display information about the tutorial.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tutorialbooking\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Used by the tutorial booking renderer to display information about the tutorial.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tutorialbooking implements \renderable, \templatable {
    /** @var int The database id of the tutorial booking record. */
    public $id;

    /** @var \cm_info The Course Module info for the tutorial booking. */
    public $cm;

    /** @var \context_module The context of the tutorial booking. */
    public $context;

    /** @var int The database id of the course the tutroial booking belongs to. */
    public $course;

    /** @var bool Stores how the text for the renderable should be formatted. */
    protected $external;

    /** @var string The name of the tutorial booking activity. */
    public $name;

    /** @var string The formatted summary of the tutorial booking. */
    public $summary;

    /** @var bool Sotres if users can signup by themselves. */
    public $locked;

    /** @var int The time at which the tutorial booking was created. */
    public $timecompleted;

    /** @var int The time when the tutorialbooking was last modified. */
    public $timemodified;

    /** @var int Stores if other students should be able to see who else has signed up. */
    public $privacy;

    /** @var bool Stores if the user can sign up to a tutorial booking activity. */
    public $cansignup = false;

    /** @var bool Stores if the user is able to add students to a tutorial slot. */
    public $canaddstudent = false;

    /** @var bool Stores if the user is able to remove students from a tutorial slot. */
    public $canremoveusers = false;

    /** @var bool Stores if the user can oversubscribe users. */
    public $canoversubscribe = false;

    /** @var \stdClass Stored the config settings for the tutorialbooking plugin. */
    public $cfg;

    /** @var bool If true the tutorial booking activity is in admin mode. */
    public $editing;

    /** @var bool Stores if the users should be able to edit signup lists. */
    public $editsignuplists = false;

    /** @var bool Stores if the user can export information from this tutorial booking activity. */
    public $editexport = false;

    /** @var bool Stores if the the user can view messages sent by the activity. */
    public $editmessage = false;

    /** @var bool Stores if the user is able to view and print registers. */
    public $editregisters = false;

    /** @var bool Stores if the user can export information for all tutorial bookings on the course. */
    public $exportall = false;

    /** @var int The id of the session the user is signed up to. */
    public $mysignup;

    /** @var int The total number of sessions on the tutorial booking. */
    public $totalsessions;

    /** @var int The number of slots in the tutorial booking. */
    public $totalspaces = 0;

    /** @var int The number of users that have signed up to the tutorial booking. */
    public $totalsignups = 0;

    /**
     * Stores if the user has the capability to edit.
     * If false many other permissions here will be set to false as well.
     *
     * @var bool
     */
    public $viewadmin = false;

    /** @var \mod_tutorialbooking\output\slot[] An array of slots indexed by their database id. */
    public $slots;

    /**
     * Gets a tutorial booking renderable for the Tutorial.
     *
     * @param \cm_info $cm
     * @param bool $external If true the text will be formatted for external tools.
     * @return \mod_tutorialbooking\output\tutorialbooking
     */
    public static function get(\cm_info $cm, bool $external = false) : tutorialbooking {
        global $DB;
        if ($cm->modname !== 'tutorialbooking') {
            throw new \coding_exception('The cm_info record must be a tutorial booking activity');
        }
        $context = \context_module::instance($cm->id);
        $tutorialid = $cm->instance;
        $tutorial = $DB->get_record('tutorialbooking', ['id' => $tutorialid], '*', MUST_EXIST);

        $renderable = static::build_from_record($tutorial, $context, $external);
        $renderable->cm = $cm;
        $renderable->load_slots();
        $renderable->load_signups();

        return $renderable;
    }

    /**
     * Adds the basic tutorial booking information to the renderable.
     *
     * @param \stdClass $record A Tutorialbooking database record
     * @param \context_module $context the context of the Tutorial booking.
     * @param bool $external If true the text will be formatted for external tools.
     * @return \mod_tutorialbooking\output\tutorialbooking
     */
    protected static function build_from_record(\stdClass $record, \context_module $context, bool $external = false) : tutorialbooking {
        $renderable = new tutorialbooking();
        $renderable->external = $external;
        $renderable->context = $context;
        $renderable->id = $record->id;
        $renderable->course = $record->course;
        $renderable->name = format_string($record->name, true, ['context' => $context]);
        if ($external) {
            list($renderable->summary, $summaryformat) =
                external_format_text($record->intro, $record->introformat, $context->id, 'mod_tutorialbooking', 'intro');
        } else {
            $renderable->summary = format_module_intro('tutorialbooking', $record, $context->instanceid);
        }
        $renderable->locked = $record->locked;
        $renderable->timecreated = $record->timecreated;
        $renderable->timemodified = $record->timemodified;
        $renderable->completionsignedup = $record->completionsignedup;
        $renderable->cfg = get_config('tutorialbooking');
        switch($record->privacy) { // Ensure there is a valid privacy value.
            case \mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS:
            case \mod_tutorialbooking_tutorial::PRIVACY_SHOWOWN:
                // These are all valid so make no changes.
                $renderable->privacy = $record->privacy;
                break;
            default:
                // Default to show signups.
                $renderable->privacy = \mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS;
                break;
        }
        // Can the user signup.
        $renderable->cansignup = has_capability('mod/tutorialbooking:submit', $context);

        // Required to edit.
        $viewadmin = has_capability('mod/tutorialbooking:viewadminpage', $context);
        $renderable->viewadmin = $viewadmin;

        // Check and set permissions.
        $renderable->editsignuplists = $viewadmin && has_capability('mod/tutorialbooking:editsignuplist', $context);
        $renderable->editexport = $viewadmin && has_capability('mod/tutorialbooking:export', $context);
        $renderable->editmessage = $viewadmin && has_capability('mod/tutorialbooking:message', $context);
        $renderable->exportall = $viewadmin && has_capability('mod/tutorialbooking:exportallcoursetutorials', $context);

        // Session capabilities.
        $renderable->editregisters = $viewadmin && has_capability('mod/tutorialbooking:printregisters', $context);
        $renderable->canaddstudent = $viewadmin && has_capability('mod/tutorialbooking:adduser', $context);
        $renderable->canremoveusers = $viewadmin && has_capability('mod/tutorialbooking:removeuser', $context);
        $renderable->canoversubscribe = $viewadmin && has_capability('mod/tutorialbooking:oversubscribe', $context);

        return $renderable;
    }

    /**
     * Loads the slots for the tutorial booking.
     *
     * @return void
     */
    protected function load_slots() {
        global $DB;
        if (isset($this->slots)) {
            // Already loaded.
            return;
        }
        $this->totalspaces = 0;
        $this->slots = array();
        $slots = \mod_tutorialbooking_tutorial::gettutorialsessions($this->id);
        foreach ($slots as $slot) {
            $this->slots[$slot->id] = slot::create_from_record($slot, $this, $this->external);
            $this->totalspaces += $this->slots[$slot->id]->spaces;
        }
        $this->totalsessions = count($this->slots);
    }

    /**
     * Loads the signups for the tutorial booking.
     */
    protected function load_signups() {
        global $DB, $USER;
        $signupselect = 'u.id AS uid, u.username, u.firstname, u.lastname, u.firstnamephonetic, 
                u.lastnamephonetic, u.middlename, u.alternatename, t.*';
        $signuporder = 'u.lastname, u.firstname';
        $sql = "SELECT $signupselect 
                  FROM {user} u
            INNER JOIN {tutorialbooking_signups} t ON  t.userid = u.id
                 WHERE t.tutorialid = :tutorialid 
              ORDER BY $signuporder";
        $signups = $DB->get_records_sql($sql, ['tutorialid' => $this->id]);

        $this->totalsignups = 0;
        foreach ($signups as $signup) {
            if (isset($this->slots[$signup->sessionid])) {
                $slot = $this->slots[$signup->sessionid];
                $slot->signups[$signup->id] = signup::create_from_record($signup, $slot);
                $this->totalsignups++;
                if ($USER->id == $signup->userid) {
                    $this->mysignup = $signup->sessionid;
                }
            } else {
                // Should not really happen, but just in case.
                debugging("Invalid slot ({$signup->sessionid}) detected", DEBUG_DEVELOPER);
            }
        }
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
        $adminparams = ['tutorialid' => $this->id, 'courseid' => $this->course];
        $adminpage = new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', $adminparams);
        $indexpage = '/mod/tutorialbooking/index.php';
        $sesskey =
        $tutorial = (object) array(
            'id' => $this->id,
            'name' => $this->name,
            'summary' => $this->summary,
            'locked' => $this->locked,
            'totalsessions' => $this->totalsessions,
            'totalsignups' => $this->totalsignups,
            'totalspaces' => $this->totalspaces,
            'cansignup' => $this->cansignup,
            'viewadmin' => $this->viewadmin,
            'editsignuplists' => $this->editsignuplists,
            'editexport' => $this->editexport,
            'editmessage' => $this->editmessage,
            'exportall' => $this->exportall,
            'editregisters' => $this->editregisters,
            'canaddstudent' => $this->canaddstudent,
            'canremoveusers' => $this->canremoveusers,
            'slots' => [],
            'urladdslot' => new \moodle_url($adminpage, ['action' => 'edit', 'id' => 0]),
            'urlviewmessages' => new \moodle_url($adminpage, ['action' => 'viewmessages']),
            'urlexport' => new \moodle_url('/mod/tutorialbooking/export.php', ['id' => $this->context->instanceid]),
            'urlalltutorials' => new \moodle_url($indexpage,['id' => $this->course]),
            'privatesignups' => $this->privacy == \mod_tutorialbooking_tutorial::PRIVACY_SHOWOWN,
            'cmid' => $this->cm->id,
        );

        if ($this->locked) {
            $tutorial->urllock = new \moodle_url($adminpage, array('action' => 'unlock'));
        } else {
            $tutorial->urllock = new \moodle_url($adminpage, array('action' => 'lock'));
        }

        foreach ($this->slots as $slot) {
            $tutorial->slots[] = $slot->export_for_template($output);
        }

        if ($this->locked == true) {
            $tutorial->urllock = new \moodle_url($adminpage, array('action' => 'unlock'));
        } else {
            $tutorial->urllock = new \moodle_url($adminpage, array('action' => 'lock'));
        }

        return $tutorial;
    }
}
