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
 * Page that will export data for signup sheets using the data format API.
 *
 * @package    mod_tutorialbooking
 * @copyright  2019 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(dirname(__DIR__)) . '/config.php');
require_once($CFG->libdir . '/dataformatlib.php');

$id = required_param('id', PARAM_INT);
$paramname = 'dataformat';
$dataformat = optional_param($paramname, '', PARAM_ALPHA);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'tutorialbooking');
$context = context_module::instance($cm->id);

// Check that user should be here.
require_course_login($course, true, $cm);
require_capability('mod/tutorialbooking:export', $context);

// Setup the page.
$pageurl = new moodle_url('/mod/tutorialbooking/export.php');
$pageparams = ['id' => $id];
$PAGE->set_url($pageurl, $pageparams);
$PAGE->set_context($context);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('exportlistprompt', 'mod_tutorialbooking'));

if (!empty($dataformat)) {
    // Log that the data is being exported.
    $eventdata = [
        'context' => $context,
        'objectid' => $cm->instance,
    ];
    $event = \mod_tutorialbooking\event\tutorial_exported::create($eventdata);
    $event->trigger();

    if ($dataformat === 'uonbs') {
        // Hack alert. This is our custom export format on this page so it
        // continues to export as before we need to always use a specific user fullname format.
        \mod_tutorialbooking\export::$uonbs = true;
    }

    // Do the export.
    $data = \mod_tutorialbooking\export::getexport($cm->instance);
    $columns = [
        'SessionTitle' => get_string('timeslottitle', 'mod_tutorialbooking'),
        'IDNumber' => get_string('idnumber'),
        'UserName' => get_string('username'),
        'RealName' => get_string('fullname'),
        'CourseFullname' => get_string('fullnamecourse'),
    ];
    $callback = [\mod_tutorialbooking\export::class, 'format_record'];
    download_as_dataformat('signup_sheet_export', $dataformat, $columns, $data, $callback);
    exit;
}

// Let the user select the export format.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('exportlistprompt', 'mod_tutorialbooking'));
echo $OUTPUT->download_dataformat_selector(get_string('selectformat', 'mod_tutorialbooking'), $pageurl, $paramname, $pageparams);
echo $OUTPUT->footer();
