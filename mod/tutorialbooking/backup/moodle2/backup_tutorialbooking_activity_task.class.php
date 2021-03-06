<?php
// This file is part of Moodle - http://moodle.org/
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
 * Backup for the Tutorial booking activity.
 *
 * @package   mod_tutorialbooking
 * @category  backup
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/tutorialbooking/backup/moodle2/backup_tutorialbooking_stepslib.php'); // Because it exists.

/**
 * Backup task that provides all the settings and steps to perform one complete backup of a Tutorial booking.
 *
 * @author    Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @copyright University of Nottingham, 2012
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_tutorialbooking_activity_task extends backup_activity_task {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step.
        $this->add_step(new backup_tutorialbooking_activity_structure_step('tutorialbooking_structure', 'tutorialbooking.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string
     */
    static public function encode_content_links($content) {
        return $content;
    }
}
