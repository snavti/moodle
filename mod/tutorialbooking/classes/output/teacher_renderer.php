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
 * Renderer for the teacher page.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_tutorialbooking\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderer for the teacher page.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class teacher_renderer extends \plugin_renderer_base {
    /**
     * Display a page with a link back to the page the user was on.
     *
     * @param string $message The message to be displayed to the user.
     * @param \moodle_url $returnurl A url that the user will be sent to when they click on the return link.
     * @return void
     */
    public function back_to_session($message, \moodle_url $returnurl) {
        echo $this->header();
        echo \html_writer::div($message);
        $linktext = \html_writer::empty_tag('br').'['.get_string('backtosession', 'tutorialbooking').']';
        echo \html_writer::link($returnurl, $linktext);
        echo $this->footer();
    }

    /**
     * Function to display a confirmation screen on deletion of a session (signup list).
     *
     * @global \moodle_page $PAGE The page object.
     * @param int $courseid The course id - always a valid value.
     * @param int $tutorialid The tutorial id - also always a valid id.
     * @param int $sessionid The sessionid 0 if tutorial is being deleted - otherwise a valid id.
     * @param string $title Title to display on the page - usually the tutorial/session name.
     * @return void Outputs confirmation page.
     */
    public function deleteconfirm($courseid, $tutorialid, $sessionid, $title) {
        global $PAGE;

        if (!$sessionid) {
            throw new \coding_exception('Session id required for deletion');
        }

        $cancelaction = $PAGE->url->out();
        // Query string stripped then rebuilt to avoid double escaping.
        $confirmaction = new \moodle_url(strip_querystring($PAGE->url),
            array (
                'courseid' => $courseid,
                'tutorialid' => $tutorialid,
                'sessionid' => $sessionid,
                'action' => 'confirmdelete'
            )
        );

        $stats = \mod_tutorialbooking_session::getsessionstats($sessionid);

        $statsline = \html_writer::tag('p',
            \html_writer::tag('strong', get_string('statsline', 'tutorialbooking', $stats))
        );

        echo $this->header();
        echo $this->heading(get_string('deletepageheader', 'tutorialbooking'), 2, 'helptitle', 'uniqueid');
        echo $this->confirm(get_string('deletewarningtext', 'tutorialbooking', $title).$statsline, $confirmaction, $cancelaction);
        echo $this->footer();
    }

    /**
     * Display the form to add users to a tutorial session,
     * unfortunatly the user select contral does not seem to
     * work with standard Moodle forms or I would have used them.
     *
     * @param stdClass $session The database recort for the tutorial slot the users are being added to.
     * @param stdClass $tutorial The database record for the tutorial booking activity the session is in.
     * @return void
     */
    public function display_addform($session, $tutorial) {
        $options = array('tutorialid' => $session->tutorialid, 'multiselect' => 1);
        $userselect = new \mod_tutorialbooking_session_add_user('addtosession', $options);

        $returnurl = new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php',
            array('tutorialid' => $tutorial->id,
                'courseid' => $tutorial->course,
                'id' => $session->id,
                'action' => 'adduserconfirm'));

        $buffer = '';
        $buffer .= $userselect->display(true);
        // The submit button.
        $buffer .= \html_writer::empty_tag('input',
            array('type' => 'submit',
                'name' => 'addtosession_add',
                'id' => 'addtosession_add',
                'value' => get_string('addstudents', 'tutorialbooking')
            ));

        echo $this->header();
        echo \html_writer::tag('form', $buffer, array('method' => 'post', 'action' => $returnurl->out(false)));
        echo $this->footer();
    }

    /**
     * Used by displaymessagelist() to generate a link to allow users to choose
     * between seeing all  messages and only their own, if they have the capability
     * to see all messages sent.
     *
     * @param \stdClass $messagestore Object containing a list of messages and other information to be rendered.
     * @return void
     */
    protected function display_filter_link(\stdClass $messagestore) {
        // Display a link to change the filter.
        if ($messagestore->can_view_all) {
            if ($messagestore->viewallmessages == \mod_tutorialbooking_message::VIEWALLMESSAGES) {
                $url = new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', array(
                    'action' => 'viewmessages',
                    'tutorialid' => $messagestore->tutorialid,
                    'courseid' => $messagestore->courseid,
                    'messages' => $messagestore->maxrecords,
                    'page' => 0,
                ));
                $filtertext = get_string('showmymessages', 'mod_tutorialbooking');
            } else {
                $url = new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', array(
                    'action' => 'viewmessages',
                    'tutorialid' => $messagestore->tutorialid,
                    'courseid' => $messagestore->courseid,
                    'messages' => $messagestore->maxrecords,
                    'page' => 0,
                    'filter' => \mod_tutorialbooking_message::VIEWALLMESSAGES,
                ));
                $filtertext = get_string('showallmessages', 'mod_tutorialbooking');
            }
            echo \html_writer::tag('p', \html_writer::link($url, $filtertext));
        }
    }

    /**
     * Displays messages sent via the activity.
     *
     * @param \stdClass $messagestore Object containing a list of messages and other information to be rendered.
     * @return void
     */
    public function displaymessagelist(\stdClass $messagestore) {
        // Page setup stuff.
        echo $this->header();
        echo $this->heading(get_string('sessionpagetitle', 'tutorialbooking'), 2);

        $this->display_filter_link($messagestore);

        echo $this->render_from_template('mod_tutorialbooking/messages', $messagestore->messages);

        $url = new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', array(
            'action' => 'viewmessages',
            'tutorialid' => $messagestore->tutorialid,
            'courseid' => $messagestore->courseid,
            'messages' => $messagestore->maxrecords,
            'filter' => $messagestore->viewallmessages,
        ));

        // Display a paging bar.
        echo $this->paging_bar($messagestore->totalmessages,
            $messagestore->page,
            $messagestore->maxrecords,
            $url,
            'page');

        // Draw the page footer.
        echo $this->footer();
    }

    /**
     * Renders a signup for a teacher.
     *
     * @param \mod_tutorialbooking\output\signup $signup
     * @return bool|string
     */
    public function render_signup(signup $signup) {
        $data = $signup->export_for_template($this);
        return $this->render_from_template('mod_tutorialbooking/teacher_signup', $data);
    }

    /**
     * Renders a slot for a teacher.
     *
     * @param \mod_tutorialbooking\output\slot $slot
     * @return bool|string
     */
    public function render_slot(slot $slot) {
        $data = $slot->export_for_template($this);
        return $this->render_from_template('mod_tutorialbooking/teacher_slot', $data);
    }

    /**
     * Renders a tutorial booking view for the teacher.
     *
     * @param \mod_tutorialbooking\output\tutorialbooking $tutorialbooking
     * @return bool|string
     */
    public function render_tutorialbooking(tutorialbooking $tutorialbooking) {
        $data = $tutorialbooking->export_for_template($this);
        return $this->render_from_template('mod_tutorialbooking/teacher_tutorialbooking', $data);
    }
}
