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
 * Defines the Moodle mobile plugins provided.
 *
 * @package    mod_tutorialbooking
 * @copyright  2017 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = array(
    'mod_tutorialbooking' => array(
        'handlers' => array(
            'tutorialbooking' => array(
                'displaydata' => array(
                    'icon' => $CFG->wwwroot . '/mod/tutorialbooking/pix/icon.svg',
                    'class' => '',
                ),
                'delegate' => 'CoreCourseModuleDelegate',
                'method' => 'tutorialbooking',
                'offlinefunctions' => array(),
            ),
        ),
        'lang' => array(
            ['freespaces' , 'tutorialbooking'],
            ['lockedprompt', 'tutorialbooking'],
            ['oversubscribedby', 'tutorialbooking'],
            ['removefromslot' , 'tutorialbooking'],
            ['signupforslot' , 'tutorialbooking'],
            ['totalspaces', 'tutorialbooking'],
            ['usedspaces' , 'tutorialbooking'],
            ['yousignedup' , 'tutorialbooking'],
        ),
    ),
);
