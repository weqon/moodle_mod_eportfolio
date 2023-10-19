<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_eportfolio
 * @category    upgrade
 * @copyright   2023 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_eportfolio upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_eportfolio_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.
    if ($oldversion < 2023080301) {

        // Define field duedate to be added to eportfolio.
        $table = new xmldb_table('eportfolio');
        $field = new xmldb_field('duedate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'name');

        // Conditionally launch add field duedate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field grade to be added to eportfolio.
        $table = new xmldb_table('eportfolio');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'duedate');

        // Conditionally launch add field grade.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_mod_savepoint(true, 2023080301, 'eportfolio');
    }

    if ($oldversion < 2023080302) {

        // Define table eportfolio_grade to be created.
        $table = new xmldb_table('eportfolio_grade');

        // Adding fields to table eportfolio_grade.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('instance', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('graderid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        // Adding keys to table eportfolio_grade.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for eportfolio_grade.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Eportfolio savepoint reached.
        upgrade_mod_savepoint(true, 2023080302, 'eportfolio');
    }

    if ($oldversion < 2023080303) {

        // Define field itemid to be added to eportfolio_grade.
        $table = new xmldb_table('eportfolio_grade');
        $field = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'instance');

        // Conditionally launch add field itemid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_mod_savepoint(true, 2023080303, 'eportfolio');
    }

    if ($oldversion < 2023080304) {

        // Define field feedbacktext to be added to eportfolio_grade.
        $table = new xmldb_table('eportfolio_grade');
        $field = new xmldb_field('feedbacktext', XMLDB_TYPE_TEXT, null, null, null, null, null, 'grade');

        // Conditionally launch add field feedbacktext.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_mod_savepoint(true, 2023080304, 'eportfolio');
    }

    return true;
}
