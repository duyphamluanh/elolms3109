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

namespace block_elo_remind_teacher_via_mail\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

class main implements renderable, templatable
{
    const SENDMAIL_DELAY = 1800;

    public $teachers;
    public $data;
    /**
     * main constructor.
     */
    public function __construct($teachers)
    {
        $this->teachers = $teachers;
        $this->table_history_rows = [];
        $this->table_teachers_rows = [];
    }


    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output)
    {
        $this->data =  [
            'table_history_rows' => $this->get_table_mail_history_rows(),
            'table_teachers_rows' =>  $this->get_table_teachers_rows()
        ];
        return $this->data;
    }

    public function get_table_teachers_rows()
    {
        $rowData = [];
        $STT = 0;
        foreach ($this->teachers as $teacher) {
             // Get teachers courses - courses which have not expired.
            $teacher_courses = getEnrolledCourseByUserID($teacher->id);
            if(!$teacher_courses){
                continue;
            }

            // Get render Not graded yet activities cells and total number of not graded yet activities
            [$coursesList,$notgradedActivitiesCoursesTotal] = $this->notGradedItemsCourseList($teacher_courses,$teacher->id);

            // Disable button if the latest mail sent time is smaller than the present time
            // 30 minutes
            $sendmaildelay = $this->SENDMAIL_DELAY;
            if (isset(get_config("block_elo_remind_teacher_via_mail")->sendmaildelay)) {
                $sendmaildelay = get_config("block_elo_remind_teacher_via_mail")->sendmaildelay * 60;
            }
            // Get the latest sent mail  to the teacher
            $mailsend = getLastSendMailTime($teacher->id);
            // Show if the latest sent mail is exists
            $maxtimecreated = $mailsend->maxtimecreated ? date('d/m/Y H:i:s', $mailsend->maxtimecreated) : '';
            $abletosendmail = (time() - $mailsend->maxtimecreated) > $sendmaildelay ? false : true;
            // Show send mail button if not graded yet activities is bigger than zero.


            if($notgradedActivitiesCoursesTotal != 0){
                array_push($rowData, [
                    'stt' => $STT,
                    'fullname' => $teacher->lastname.' '.$teacher->firstname,
                    'email' => $teacher->email,
                    'teacherid' => $teacher->id,
                    'courseslist'=> $coursesList,
                    'total' => $notgradedActivitiesCoursesTotal,
                    'maxtimecreated' => $maxtimecreated,
                    'abletosendmail' => $abletosendmail
                ]);
                $STT++;
            }

        }
        return $rowData;
    }

    public function get_table_mail_history_rows(){
        $rowData = [];
        $STT = 0;
        $mailssent = getMaiLHistory();
        foreach($mailssent as $mailsent){
            $userFrom = getUserById($mailsent->userfromid);
            $userTo = getUserById($mailsent->usertoid);
            array_push($rowData, [
                'stt' => $STT,
                'userfrom' => $userFrom->lastname.' '.$userFrom->firstname,
                'userto' => $userTo->lastname.' '.$userTo->firstname,
                'content' => $mailsent->content,
                'timecreated' => date('d/m/Y H:i:s', $mailsent->timecreated),
                'timecreatedforsort' =>  $mailsent->timecreated
            ]);
            $STT++;
        }
        return $rowData;
    }

    public function notGradedItemsCourseList($teacher_courses, $teacherid){
        $notgradedActivitiesCoursesTotal = 0;
        $coursesList = [];
        foreach ($teacher_courses as $teacher_course) {
            [$notgradedActivities, $notgradedActivitiesTotal] = $this->notGradedItemsList($teacher_course->id, $teacherid);
            if ($notgradedActivitiesTotal == 0) continue;
            $teacher_course->notgradedActivities = $notgradedActivities;
            array_push($coursesList, $teacher_course);
            $notgradedActivitiesCoursesTotal += $notgradedActivitiesTotal;
        }
        return [$coursesList, $notgradedActivitiesCoursesTotal];
    }

    public function notGradedItemsList($courseid,$teacherid){
        $gradedActivities = getGradedActivitiesByCourseID($courseid,$teacherid);
        $notgradedActivitiesTotal = 0;
        foreach($gradedActivities as $gradedActivitie){
            $notgradedActivitiesTotal += $gradedActivitie->notgradedActivities;
        }
        return [$gradedActivities,$notgradedActivitiesTotal];
    }

}
