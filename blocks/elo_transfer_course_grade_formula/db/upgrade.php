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
 * Version details
 *
 * @package    block_elo_tranfers_course_grade_formula
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Handles upgrading instances of this block.
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_elo_transfer_course_grade_formula_upgrade($oldversion) {
    global $CFG,$DB;
 
    $result = TRUE;
//     $dbman = $DB->get_manager();
 
// // Insert PHP code from XMLDB Editor here
//     if ($oldversion < 2021011302) {

//         // Define field id to be added to duy_block_table.
//         $table = new xmldb_table('duy_block_table');
//         $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);

//         // Conditionally launch add field id.
//         if (!$dbman->field_exists($table, $field)) {
//             $dbman->add_field($table, $field);
//         }

//         // elo_tranfers_course_grade_formula savepoint reached.
//         upgrade_block_savepoint(true, 2021011302, 'elo_tranfers_course_grade_formula');
//     }

 
    return $result;
}
?>