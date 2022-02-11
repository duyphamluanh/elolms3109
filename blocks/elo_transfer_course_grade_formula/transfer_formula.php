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
 * Wrapper script redirecting user operations to correct destination.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/elo_transfer_course_grade_formula/lib.php');

$contextper = context_course::instance($COURSE->id);
$permissionaccesslink = has_capability('moodle/role:switchroles', $contextper);
$returnurl = $CFG->wwwroot . '/my/';
if (!$permissionaccesslink) {
    redirect($returnurl); // Khi khong co quyen thi khong the truy cap link truc tiep bang duong dan
}

$url = new moodle_url('/blocks/elo_transfer_course_grade_formula/index.php');
$PAGE->set_url($url);

$PAGE->set_context(context_system::instance());
$title = get_string('blocktitle', 'block_elo_transfer_course_grade_formula');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);

echo $OUTPUT->header();
echo $OUTPUT->container_start('block_elo_transfer_course_grade_formula');
require_login();

// handling data
$params1 = required_param('orgcourseid', PARAM_TEXT);
$params2 = required_param('transfercoursesid', PARAM_TEXT);
$forceupdate = optional_param('forceupdate', false ,PARAM_BOOL);
$origincourse = $params1;
$transfercourses = explode(",", $params2);
if (($key = array_search($origincourse, $transfercourses)) !== false) {
    unset($transfercourses[$key]);
}

// add progressbar
$progressbar = new progress_bar();
$progressbar->create();
core_php_time_limit::raise(HOURSECS);
raise_memory_limit(MEMORY_EXTRA);

echo transfer_course($origincourse, $transfercourses, $progressbar, $forceupdate);

echo $OUTPUT->container_end();
echo $OUTPUT->footer();
