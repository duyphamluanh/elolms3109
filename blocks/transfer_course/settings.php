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
 * @package    block_transfer_course
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
// Nhien 2020_09_14
    require_once($CFG->dirroot . '/blocks/transfer_course/lib.php');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_transfer_course_getthubayvachunhatisvacation', get_string('getthubayvachunhatisvacationtitle', 'block_transfer_course'),
                       get_string('getthu7andchunhatisvacation', 'block_transfer_course'), 0));
}


