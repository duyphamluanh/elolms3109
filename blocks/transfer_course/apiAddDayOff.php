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
 * @copyright 2021 ou.edu.vn
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/transfer_course/lib.php');
ob_start();
$dayoff_day = required_param('dayoff_day', PARAM_TEXT);
$dayoff_month = required_param('dayoff_month', PARAM_TEXT);
$dayoff_year = required_param('dayoff_year', PARAM_TEXT);
$dayoff_yearschool = required_param('dayoff_yearschool', PARAM_TEXT);
$event = required_param('dayoff_event', PARAM_TEXT);
$semester = required_param('dayoff_semester', PARAM_TEXT);

global $CFG,$DB;
$dayoff_date = $dayoff_day.' '.$dayoff_month.' '.$dayoff_year;
$dayoff_timestamp = strtotime($dayoff_date);
$dayoff = array(
    'date' => $dayoff_timestamp,
    'event' => $event,
    'year' => $dayoff_yearschool,
    'semester' => (int)$semester,
);

$num = $DB->count_records('transfer_course_dayoff', ['date' => $dayoff_timestamp]);
if($num == 0){
    $result = $DB->insert_record('transfer_course_dayoff', $dayoff, $returnid=true, $bulk=false);
    ob_end_clean();
    echo json_encode($dayoff_timestamp);
}else{
    http_response_code(406);
    echo json_encode("Record existed");
}

