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
 * Output views for the Moodle mobile app.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tutorialbooking\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Moblie output class.
 *
 * @see https://docs.moodle.org/dev/Mobile_support_for_plugins#Step_2._Creating_the_main_function
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * Generates the session view for the mobile app.
     *
     * @param array $args Arguments from tool_mobile_get_content web service.
     * @return array HTML, javascript and otherdata
     */
    public static function tutorialbooking(array $args) : array {
        global $PAGE;
        $args = (object) $args;
        $cm = get_fast_modinfo($args->courseid)->get_cm($args->cmid);
        if ($cm->modname !== 'tutorialbooking') {
            throw new \coding_exception('invalid_module');
        }
        // Capabilities check.
        require_login($args->courseid, false, $cm, true, true);
        $tutorialbooking = tutorialbooking::get($cm, true);
        $output = $PAGE->get_renderer('mod_tutorialbooking', 'mobile');

        return array(
            'templates' => array(
                array(
                    'id' => 'main',
                    'html' => $output->render($tutorialbooking),
                ),
            ),
            'javascript' => '',
            'otherdata' => '',
            'files' => [],
        );
    }
}