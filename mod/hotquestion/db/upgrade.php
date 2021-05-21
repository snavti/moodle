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
 * Upgrade code for install
 *
 * @package   mod_hotquestion
 * @copyright 2012 Zhang Anzhen
 * @copyright 2016 onwards AL Rachels drachels@drachels.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade this hotquestion instance - this function could be skipped but it will be needed later.
 * @param int $oldversion The old version of the hotquestion module
 * @return bool
 */
function xmldb_hotquestion_upgrade($oldversion=0) {
    global $CFG, $DB;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.
    $result = true;

    // 1.9.0 Upgrade line.
    if ($result && $oldversion < 2007040100) {

        // Define field course to be added to hotquestion.
        $table = new XMLDBTable('hotquestion');
        $field = new XMLDBField('course');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'id');
        // Launch add field course.
        $result = $result && $table->add_field($field);

        // Define field intro to be added to hotquestion.
        $field = new xmldb_field('intro');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'name');

        // Launch add field intro.
        $result = $result && $table->add_field($field);

        // Define field introformat to be added to hotquestion.
        $field = new xmldb_field('introformat');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'intro');
        // Launch add field introformat.
        $result = $result && $table->add_field($field);
    }

    if ($result && $oldversion < 2007040101) {

        // Define field timecreated to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('timecreated');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'introformat');

        // Launch add field timecreated.
        $result = $result && $table->add_field($field);

        $field = new xmldb_field('timemodified');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'timecreated');
        // Launch add field timemodified.
        $result = $result && $table->add_field($table, $field);

        // Define index course (not unique) to be added to hotquestion.
        $result = $result && $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
    }

    if ($result && $oldversion < 2007040200) {
        // Add some actions to get them properly displayed in the logs.
        $rec = new stdClass;
        $rec->module = 'hotquestion';
        $rec->action = 'add';
        $rec->mtable = 'hotquestion';
        $rec->filed  = 'name';
        // Insert the add action in log_display.
        $result = $DB->insert_record('log_display', $rec);
        // Now the update action.
        $rec->action = 'update';
        $result = $DB->insert_record('log_display', $rec);
        // Now the view action.
        $rec->action = 'view';
        $result = $DB->insert_record('log_display', $rec);
    }

    // 3.1.0 Upgrade start here.
    if ($oldversion < 2016100300) {

        // Define field submitdirections to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('submitdirections', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL,
            null, 'Submit your question here', 'introformat');

        // Conditionally launch add field submitdirections.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timeopen to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('timeopen', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field timeopen.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timeclose to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('timeclose', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timeopen');

        // Conditionally launch add field timeclose.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2016100300, 'hotquestion');
    }

    // 3.3.2 Upgrade start here.
    if ($oldversion < 2017122500) {

        // Define field approval to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('approval', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'anonymouspost');

        // Conditionally launch add field approval.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field approved to be added to hotquestion_questions.
        $table = new xmldb_table('hotquestion_questions');
        $field = new xmldb_field('approved', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'anonymous');

        // Conditionally launch add field approval.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2017122500, 'hotquestion');
    }

    // 3.4.0 Upgrade start here.
    if ($oldversion < 2018010100) {
        // Define field teacherpriority to be added to hotquestion_questions.
        $table = new xmldb_table('hotquestion_questions');
        $field = new xmldb_field('tpriority', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'approved');

        // Conditionally launch add field teacherpriority.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2018010100, 'hotquestion');
    }

    if ($oldversion < 2019112100) {

        // Define field teacherpriorityvisibility to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('teacherpriorityvisibility', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'timeclose');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field heatvisibility to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('heatvisibility', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1',
            'teacherpriorityvisibility');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2019112100, 'hotquestion');
    }
    if ($oldversion < 2020051000) {

        // Define field questionlabel to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('questionlabel', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'Questions', 'timeclose');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field teacherprioritylabel to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('teacherprioritylabel', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL,
            null, 'Priority', 'teacherpriorityvisibility');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field heatlabel to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('heatlabel', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'Heat', 'heatvisibility');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field approvallabel to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('approvallabel', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'Approved', 'approval');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field removelabel to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('removelabel', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'Remove', 'approvallabel');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2020051000, 'hotquestion');
    }

    if ($oldversion < 2020052800) {

        // Define field heatlimit to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('heatlimit', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'heatlabel');

        // Conditionally launch add field heatlimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2020052800, 'hotquestion');
    }

    if ($oldversion < 2020121700) {

        // Define field authorhide to be added to hotquestion.
        $table = new xmldb_table('hotquestion');
        $field = new xmldb_field('authorhide', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'anonymouspost');

        // Conditionally launch add field authorhide.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2020121700, 'hotquestion');
    }

    if ($oldversion < 2021032300) {

        // Define field format to be added to hotquestion_questions.
        $table = new xmldb_table('hotquestion_questions');
        $field = new xmldb_field('format', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'content');

        // Conditionally launch add field format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hotquestion savepoint reached.
        upgrade_mod_savepoint(true, 2021032300, 'hotquestion');
    }
    return $result;
}
