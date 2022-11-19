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
 * Form for editing tutorial session information.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");

/**
 * Form for editing tutorial session information.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_session_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;
        $current = $this->_customdata['current'];
        $title = $this->_customdata['title'];
        $tutorialid = $this->_customdata['tutorialid'];
        $courseid = $this->_customdata['courseid'];
        $summaryoptions = $this->_customdata['summaryoptions'];

        $mform->addElement('html', html_writer::tag('h2', $title, array('class' => "main help")));

        if (!$current->id) {
            $mform->addElement('header', 'session', get_string('newsessionheading', 'tutorialbooking'));
            $mform->addElement('static', 'sessionhelp', '', get_string('newsessionhelp', 'tutorialbooking', $title));
        } else {
            $mform->addElement('header', 'session', get_string('editsessionheading', 'tutorialbooking'));
            $mform->addElement('static', 'sessionhelp', '', get_string('editsessionhelp', 'tutorialbooking', $title));
        }

        $mform->addElement('hidden', 'id', $current->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'tutorialid', $tutorialid);
        $mform->setType('tutorialid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'sequence');
        $mform->setType('sequence', PARAM_RAW);
        $mform->addElement('hidden', 'usercreated');
        $mform->setType('usercreated', PARAM_RAW);
        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHA);

        // Description.
        $mform->addElement('textarea', 'description', get_string('sessiondescriptionprompt', 'tutorialbooking'));
        $mform->setType('description', PARAM_TEXT);

        // A formatable summary area.
        $mform->addElement('editor', 'summary_editor', get_string('sessionsummaryprompt', 'tutorialbooking'), '', $summaryoptions);
        $mform->setType('summary_editor', PARAM_RAW);

        $mform->addElement('static', 'sessiondescriptionhelp', '',
                html_writer::tag('strong', get_string('sessiondescriptionhelp', 'tutorialbooking')));
        $mform->addElement('static', 'sessiondescriptionhelp2', '', get_string('sessiondescriptionhelp2', 'tutorialbooking'));

        // Spaces/places.
        $mform->addElement('text', 'spaces', get_string('spacesprompt', 'tutorialbooking'), array('size' => 3));
        $mform->setType('spaces', PARAM_INT);

        if (count($this->_customdata['positions'])) {
            $mform->addElement('select', 'newposition', get_string('positionprompt', 'tutorialbooking'),
                $this->_customdata['positions']);
            $mform->setDefault('newposition', $current->sequence); // Really want bottom of the page to be the default.
            $mform->setType('newposition', PARAM_INT);
        }

        // Buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'save',
                get_string('save', 'tutorialbooking'), array('id' => 'id_submitbutton'));
        $buttonarray[] = $mform->createElement('submit', 'saveasnew',
                get_string('saveasnew', 'tutorialbooking'), array('id' => 'id_submitbutton2'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel',
                get_string('cancel', 'tutorialbooking'), array('id' => 'id_cancel'));
        if (!empty($this->_customdata['id'])) { // Existing record.
            $buttonarray[] = $mform->createElement('reset', 'resetbutton', get_string('reset', 'tutorialbooking'));
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
        // Fill in the form.
        $this->set_data($current);
    }
}
