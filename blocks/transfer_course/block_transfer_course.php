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
 * Course list block.
 *
 * @package    block_course_list
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class block_transfer_course extends block_list {
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $icon = $OUTPUT->pix_icon('i/course', get_string('course'));
        
        $hasupdatecourse =     has_capability('moodle/course:update', context_system::instance());//permission access block transfer courses
        if (!$hasupdatecourse) {
            return $this->content;
        }
        if ($hasupdatecourse && isloggedin() && !isguestuser()) {    // Just print My Courses
        /// If we can update any course of the view all isn't hidden, show the view all courses link
            $this->content->footer = $icon."<a href=\"$CFG->wwwroot/blocks/transfer_course/index.php\">".get_string("coursecatmanagement")."</a> ...";   
        }

        return $this->content;
    }
}


