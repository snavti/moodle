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
 * Renderer for the register page.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_tutorialbooking\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderer for the register page.
 *
 * @package    mod_tutorialbooking
 * @copyright  2018 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class register_renderer extends \plugin_renderer_base {
    /**
     * Load a script that will tell the browser to print the page.
     * Used by the tutorial booking register page, which does not load the normal page stuff.
     *
     * @return void
     */
    private function javascript_force_print() {
        echo \html_writer::tag('script', "<!--\nwindow.print();\n-->", array('type' => 'text/javascript'));
    }

    /**
     * Renders the register for a signup slot.
     *
     * @param html_table $register The register table to be printed.
     * @return void
     */
    public function render_register(\html_table $register) {
        echo \html_writer::table($register);
        $this->javascript_force_print();
    }

}