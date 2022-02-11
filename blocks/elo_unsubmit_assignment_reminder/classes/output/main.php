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
 * Class containing data for timeline block.
 *
 * @package    block_elo_remind_teacher_via_mail
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_elo_unsubmit_assignment_reminder\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use stdClass;
use templatable;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

class main implements renderable, templatable
{
    public $data;
    /**
     * main constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output)
    {
        $this->data = [
            'courses' => (array)$this->prepare_data_for_template(),
        ];
        return $this->data;
    }

    public function prepare_data_for_template(){
        global $USER;

        $courses = $this->get_all_unexpired_enrolled_course();
        $unsubmitassignments = [];
        foreach($courses as $course){
            $unsubmitassignment = new stdClass();
            $modinfo = get_fast_modinfo($course->id);
            $assignments = $modinfo->get_instances_of('assign');    
            foreach($assignments as $assignment){
                $submission = $this->get_assign_submission($USER->id, $assignment->instance);
                $aka = $submission->status;
                if($aka == "draft"){
                    $unsubmitassignment->assign[] = $assignment;
                }
            }
            if($unsubmitassignment->assign != null){
                $unsubmitassignment->coursename = $course->fullname;
                array_push($unsubmitassignments, $unsubmitassignment);
            }
        }


        return $unsubmitassignments;
    }

    public function get_assign_submission($userid, $assignid){
        global $DB;
        $sql = "SELECT * from {assign_submission} WHERE userid = $userid AND assignment = $assignid";
        $submission = $DB->get_record_sql($sql);
        return $submission;
    }
    
    public function get_all_unexpired_enrolled_course(){
        $courses = enrol_get_my_courses('*');
        $now = time();
        foreach($courses as $c => $course){
            if($course->enddate <  $now){
                unset($courses[$c]);
            }
        }
        return $courses;
    }
    
}
