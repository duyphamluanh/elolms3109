<?php
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->dirroot .'/mod/forum/lib.php');
// 2 weeks
const GRADING_DEADLINE = 1209600;
const SENDMAIL_DELAY = 1800;

// Functions render
// Render table
function renderTable($teachers){
    // Get table rows render
    $row = renderTableRows($teachers);
    $historyrow = renderTeachMailHistory();
    $table = '
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">'.get_string('teacherlist', 'block_elo_remind_teacher_via_mail').'</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">'.get_string('history', 'block_elo_remind_teacher_via_mail').'</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
    <div class="tab-pane active mt-4" id="home" role="tabpanel" aria-labelledby="home-tab">
        <table class="table table-bordered table-hover mt-4" id="remindteachertable">
        <thead>
            <tr>
            <th scope="col" class="text-center">'.get_string('stt', 'block_elo_remind_teacher_via_mail').'</th>
            <th scope="col" class="text-center">'.get_string('teacher', 'block_elo_remind_teacher_via_mail').'</th>
            <th scope="col" class="text-center" width=40% >'.get_string('notgradedyetactivities', 'block_elo_remind_teacher_via_mail').'</th>
            <th scope="col" class="text-center" >'.get_string('total', 'block_elo_remind_teacher_via_mail').'</th>
            <th scope="col" class="text-center">'.get_string('mail', 'block_elo_remind_teacher_via_mail').'</th>
            </tr>
        </thead>
        <tbody>
            '.$row.'
        </tbody>
        </table>
    </div>
    <div class="tab-pane mt-4" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <table class="table table-bordered table-hover mt-4" id="emailhistorytable">
            <thead>
                <tr>
                <th scope="col" class="text-center">'.get_string('stt', 'block_elo_remind_teacher_via_mail').'</th>
                 <th scope="col" class="text-center">'.get_string('userfrom', 'block_elo_remind_teacher_via_mail').'</th>
                <th scope="col" class="text-center">'.get_string('userto', 'block_elo_remind_teacher_via_mail').'</th>
                <th scope="col" class="text-center w40">'.get_string('content', 'block_elo_remind_teacher_via_mail').'</th>
                <th scope="col" class="text-center">'.get_string('time', 'block_elo_remind_teacher_via_mail').'</th>
                </tr>
            </thead>
            <tbody>
                '.$historyrow.'
            </tbody>
        </table>
    </div>
    </div>
    ';  
    return $table;
}

// Render table rows
function renderTableRows($teachers){
    global $CFG;
    $row = '';
    $STT = 0;
    foreach($teachers as $teacher){
        // Get teachers courses - courses which have not expired.
        $teacher_courses = getEnrolledCourseByUserID($teacher->id);
        // Get render Not graded yet activities cells and total number of not graded yet activities
        [$courseListHTML,$notgradedActivitiesCoursesTotal] = renderGradedItemsCourseList($teacher_courses,$teacher->id);
        if($notgradedActivitiesCoursesTotal != 0){
            $row .= '
            <tr>
                <th scope="row" class="text-center">'.$STT++.'</th>
                <td>
                    <p>'.get_string('fullname', 'block_elo_remind_teacher_via_mail').': '.$teacher->lastname.' '.$teacher->firstname.'</p>
                    <p>'.get_string('email', 'block_elo_remind_teacher_via_mail').': '.$teacher->email.'</p>
                </td>
                <td>
                    <strong>'.get_string('courses', 'block_elo_remind_teacher_via_mail').': </strong>
                    '.$courseListHTML.'
                </td>
                <td class="text-center"><strong>'.$notgradedActivitiesCoursesTotal.'</strong></td>
                <td class="text-center">';
            // Render send mail cell
            // Get the latest sent mail  to the teacher
            $mailsend = getLastSendMailTime($teacher->id);
            // Show if the latest sent mail is exists
            $maxtimecreated = $mailsend->maxtimecreated ? date('d/m/Y H:i:s', $mailsend->maxtimecreated) : '';
            // Show send mail button if not graded yet activities is bigger than zero.
            if($notgradedActivitiesCoursesTotal > 0){
                $row .= '<button type="button" class="btn btn-primary"';
                // Disable button if the latest mail sent time is smaller than the present time
                // 30 minutes
                $sendmaildelay = SENDMAIL_DELAY;
                if (isset(get_config("block_elo_remind_teacher_via_mail")->sendmaildelay)) {
                    $sendmaildelay = get_config("block_elo_remind_teacher_via_mail")->sendmaildelay * 60;
                }
                $row .= (time() - $mailsend->maxtimecreated) > $sendmaildelay ? "" : " disabled ";
                $row .= ' tid='.$teacher->id.' id="sendmail_'.$teacher->id.'">
                            <i class="fa fa-spinner fa-spin hide"></i>'.get_string('mail', 'block_elo_remind_teacher_via_mail').'</button>';
            }
                $row .= '<p id="latestSent_'.$teacher->id.'">'.get_string('latest_send_mail', 'block_elo_remind_teacher_via_mail').': '.$maxtimecreated.'</p>
                    </td>
                </tr>';
            }
        
            
    }
    return $row;
}

// Render table mails history
function renderTeachMailHistory(){
    $row = '';
    $STT = 0;
    $teacherMailsHistory = getMaiLHistory();
    foreach($teacherMailsHistory as $teacherMailHistory){
        $userFrom = getUserById($teacherMailHistory->userfromid);
        $userTo = getUserById($teacherMailHistory->usertoid);
        $row .= '
            <tr>
                <th scope="row" class="text-center">'.$STT++.'</th>
                <td>
                    <p>'.get_string('fullname', 'block_elo_remind_teacher_via_mail').': '.$userFrom->lastname.' '.$userFrom->firstname.'</p>
                    <p>'.get_string('email', 'block_elo_remind_teacher_via_mail').': '.$userFrom->email.'</p>
                </td>
                <td>
                    <p>'.get_string('fullname', 'block_elo_remind_teacher_via_mail').': '.$userTo->lastname.' '.$userTo->firstname.'</p>
                    <p>'.get_string('email', 'block_elo_remind_teacher_via_mail').': '.$userTo->email.'</p>
                </td>
                <td> '.$teacherMailHistory->content.'</td>
                <td class="text-center">'.date('d/m/Y H:i:s', $teacherMailHistory->timecreated).'</td>
                ';
    }
    return $row;
}

// Render Course list and not graded yet activities 
function renderGradedItemsCourseList($teacher_courses,$teacherid){
    $courseListHTML = '';
    $courseListHTML .= '<div id="accordion_'.$teacherid.'">';
    // Count not graded yet activities
    $notgradedActivitiesCoursesTotal = 0;
    foreach($teacher_courses as $teacher_course){
        [$gradedActivitiesHTML,$notgradedActivitiesTotal] = renderGradedItemsList($teacher_course->id,$teacherid);
        if($notgradedActivitiesTotal == 0){continue;}
        // Count not graded yet activities
        $notgradedActivitiesCoursesTotal += $notgradedActivitiesTotal;
        $courseListHTML .='<div class="card bg-light">
            <div class="" id="heading_'.$teacherid.'_'.$teacher_course->id.'">
                <h5 class="mb-0">
                    <button class="btn collapsed text-dark w-100 textselectbutton" data-toggle="collapse" data-target="#collapse_'.$teacherid.'_'.$teacher_course->id.'" aria-expanded="false" aria-controls="collapse_'.$teacherid.'_'.$teacher_course->id.'">
                    '.$teacher_course->fullname.'
                    </button>
                </h5>
            </div>
            <div id="collapse_'.$teacherid.'_'.$teacher_course->id.'" class="collapse" aria-labelledby="heading_'.$teacherid.'_'.$teacher_course->id.'" data-parent="#accordion_'.$teacherid.'">
                <div class="px-2 py-3">
                    '.$gradedActivitiesHTML.'
                </div>
            </div>
        </div>';
    }
    $courseListHTML .= '</div>';
    return [$courseListHTML,$notgradedActivitiesCoursesTotal];
}

// Render not graded yet activities list
function renderGradedItemsList($courseid,$teacherid){
    $gradedActivities = getGradedActivitiesByCourseID($courseid,$teacherid);
    $gradedActivitiesHTML = '';
    $notgradedActivitiesTotal = 0;
    foreach($gradedActivities as $gradedActivitie){
        $gradedActivitiesHTML.= '<p>'.$gradedActivitie->name.' : '.$gradedActivitie->notgradedActivities.'</p>';
        $notgradedActivitiesTotal += $gradedActivitie->notgradedActivities;
    }
    
    return [$gradedActivitiesHTML,$notgradedActivitiesTotal];
}
// Functions render - end


// Functions get data with sql
function getUserById($id){
    global $DB;
    $sql = 'SELECT *
            FROM {user}
            where id = '.$id;
    $user = $DB->get_record_sql($sql);
    return $user;
}

// Lấy những môn học chưa kết thúc dựa theo id giáo viên
function getEnrolledCourseByUserID($userid){
    global $DB;
    $sql = 'SELECT c.*
            FROM {user_enrolments} ur
            INNER JOIN {enrol} e ON e.id = ur.enrolid
            INNER JOIN {course} c ON c.id = e.courseid
            where userid = '.$userid.' and c.enddate > '.time();
    $courses = $DB->get_records_sql($sql);
    return $courses;
}

function getMaiLHistory(){
    global $DB;
    $sql = 'SELECT * FROM  {block_elo_remind_teacher}';
    $mailHistory = $DB->get_records_sql($sql);
    return $mailHistory;
}

// Lấy những bài post chưa ẩn
function getPostsByDisscussionID($disscussionID,$teacherid){
    global $DB;
    $sql = 'SELECT * FROM {forum_posts} where 
    discussion = ? 
    AND userid != ? 
    AND deleted = 0
    ';
    $posts = $DB->get_records_sql($sql,array($disscussionID,$teacherid));

    $sql = 'SELECT p.*, r.rating
            FROM {forum_posts} p
            INNER JOIN {rating} r ON r.itemid = p.id 
            where discussion = ?  AND deleted = 0';
    $gradedpost = $DB->get_records_sql($sql,array($disscussionID));
    return count($posts) - count($gradedpost);
}

// function getSubmissionsByAssignmentID($assignment,$teacherid){
//     global $DB;
//     $sql = 'SELECT * FROM {assign_submission} where assignment = ? and status = "submitted" ';
//     $posts = $DB->get_records_sql($sql,array($assignment->id));

//     $sql = 'SELECT *
//             FROM {assign_grades}
//             where assignment = ? and grade >= 0';

//     $gradedpost = $DB->get_records_sql($sql,array($assignment->id));
//     return count($posts) - count($gradedpost);
// }


function getSubmissionsByAssignmentID($assignment){
    $needgrading = elo_get_needgradingsubmissionscount($assignment->cm);
    return $needgrading;
}

function elo_get_needgradingsubmissionscount($id) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');
    $context = \context_module::instance($cm->id);
    $assign = new \assign($context, $cm, $course);
    $assignsumary = $assign->get_assign_grading_summary_renderable();
    return $assignsumary->submissionsneedgradingcount;
}

function getLastSendMailTime($teacherID){
    global $DB;
    $sql = 'SELECT userfromid,MAX(timecreated) as maxtimecreated FROM {block_elo_remind_teacher} where usertoid = ?';
    $mail = $DB->get_record_sql($sql,array($teacherID));
    return $mail;
}

// Functions get data with sql - end
function getGradedActivitiesByCourseID($courseid,$teacherid){
    global $CFG;
    $activities = get_array_of_activities($courseid);
    $acceptedGradedMods = ['forum','assign'];
    $gradedActivities = [];
    foreach( $activities as  $activitie){  
        // Only use activities with idnumber(graded activities) and mod is forum or assign
        if(array_key_exists('idnumber', $activitie) && 
            in_array($activitie->mod,$acceptedGradedMods,TRUE) ){             
            $gaptime = time() - getActivitiesExpiredDate($activitie->id, $activitie->mod);
            // 2 weeks
            $timetoshowteachers = GRADING_DEADLINE;
            if (isset(get_config("block_elo_remind_teacher_via_mail")->gaptime)) {
                $timetoshowteachers = get_config("block_elo_remind_teacher_via_mail")->gaptime * 604800;
            }
            if($gaptime < $timetoshowteachers){
                continue;
            }
            $NotGradedYetActivities = getNotGradedYetActivities($activitie,$teacherid);     
            $activitie->notgradedActivities = $NotGradedYetActivities;
            // Add link for send mail
            switch ($activitie->mod) {
                case 'forum':
                    $activitie->link = new moodle_url("/mod/forum/view.php", ['id' => $activitie->cm]);
                    break;
                case 'assign':
                    $activitie->link = new moodle_url("/mod/assign/view.php", ['id' => $activitie->cm]);
                    break;
                default:
                    break;
            }
            if($NotGradedYetActivities > 0){
                $gradedActivities[] = $activitie;
            }          
        }
    }
    return $gradedActivities;
}

function getActivitiesExpiredDate($activitiesID, $activitiesMod){
    global $DB;
    switch ($activitiesMod) {
        case 'forum':
            $sql = 'SELECT assesstimefinish FROM {forum} where id = ?';
            $expiredDate = $DB->get_record_sql($sql,array($activitiesID));
            return $expiredDate->assesstimefinish;
            break;
        case 'assign':
            $sql = 'SELECT duedate FROM {assign} where id = ?';
            $expiredDate = $DB->get_record_sql($sql,array($activitiesID));
            return $expiredDate->duedate;    
            break;
        default:
            break;
    }
}

function getNotGradedYetActivities($activitie,$teacherid){
    switch ($activitie->mod) {
        case 'forum':
            $cm = get_coursemodule_from_instance($activitie->mod, $activitie->id);
            $discussions = forum_get_discussions($cm);
            foreach($discussions as  $discussion){
                $posts = getPostsByDisscussionID($discussion->discussion,$teacherid);
            }
            return $posts != null ? $posts : 0;
            break;
        case 'assign':
            // return getSubmissionsByAssignmentID($activitie->id,$teacherid);
            return getSubmissionsByAssignmentID($activitie);
            break;
        default:
            # code...
            return 0;
            break;
    }
    return;
}   

function renderMailContent($touser,$fromuser){
    global $OUTPUT;

    $EnrolledCourses = getEnrolledCourseByUserID($touser->id);
    $EnrolledCoursesHTMl = array();
    foreach($EnrolledCourses as $EnrolledCourse){
        $course = new stdClass();
        $course->name = $EnrolledCourse-> fullname;
        $course->activities = getGradedActivitiesByCourseID($EnrolledCourse->id, $touser->id);
        $EnrolledCoursesHTMl[] = $course;
    }

    $message = new stdClass();
    $messagetextdata = [
        'fullname' => fullname($touser),
        'message' => get_string('reminder:message', 'block_elo_remind_teacher_via_mail'),
        'sign' => get_string('reminder:sign', 'block_elo_remind_teacher_via_mail'),
        'ungradedCourse' => $EnrolledCoursesHTMl
    ];

    $subject = get_string('reminder:subject', 'block_elo_remind_teacher_via_mail');

    // Render message email body.
    $messagehtml = $OUTPUT->render_from_template('block_elo_remind_teacher_via_mail/email_reminder', $messagetextdata);
    $message->fullmessage = html_to_text($messagehtml);
    $message->fullmessagehtml = $messagehtml;

    return [$subject, $message->fullmessage, $messagehtml];
}

// Send mail
function elo_send_mail_to_user($touser,$fromuser){
    // global $OUTPUT;
    
    // $EnrolledCourses = getEnrolledCourseByUserID($touser->id);
    // $EnrolledCoursesHTMl = array();
    // foreach($EnrolledCourses as $EnrolledCourse){
    //     $course = new stdClass();
    //     $course->name = $EnrolledCourse-> fullname;
    //     $course->activities = getGradedActivitiesByCourseID($EnrolledCourse->id, $touser->id);
    //     $EnrolledCoursesHTMl[] = $course;
    // }

    // $message = new stdClass();
    // $messagetextdata = [
    //     'fullname' => fullname($touser),
    //     'message' => get_string('reminder:message', 'block_elo_remind_teacher_via_mail'),
    //     'sign' => get_string('reminder:sign', 'block_elo_remind_teacher_via_mail'),
    //     'ungradedCourse' => $EnrolledCoursesHTMl
    // ];

    // $subject = get_string('reminder:subject', 'block_elo_remind_teacher_via_mail');

    // // Render message email body.
    // $messagehtml = $OUTPUT->render_from_template('block_elo_remind_teacher_via_mail/email_reminder', $messagetextdata);
    // $message->fullmessage = html_to_text($messagehtml);
    // $message->fullmessagehtml = $messagehtml;

    [$subject, $fullmessage, $messagehtml] = renderMailContent($touser,$fromuser);

    $success = email_to_user($touser, $fromuser, $subject, $fullmessage, $messagehtml);

    return $success;
}

function elo_sendmail_to_user($params){
    global $DB, $USER;
    $arrMailSucess = array();
    $arrMailFailed = array();
    
    $userbyid = core_user::get_user($params['teacherid']);    
    $flagSucess = false;
    $lastsentmail = 0;
    
    try {
        try {
            $transaction = $DB->start_delegated_transaction();
            // Do something here.

            //Send mail
            $flagSucess = $userbyid->emailstop == 0 ? elo_send_mail_to_user($userbyid,$USER) : false;
            
            // $flagSucess = true;
            $now = time();
            //Insert DB
            $dataobject = new stdClass();
            $dataobject->userfromid = $USER->id;
            $dataobject->usertoid = $userbyid->id;
            $dataobject->timecreated = $now;
            if($flagSucess === true){
                [$subject, $fullmessage, $messagehtml] = renderMailContent($userbyid,$USER);
                $dataobject->content = $messagehtml;
            }
            $index = $DB->insert_record('block_elo_remind_teacher',$dataobject);
            $lastsentmail = $dataobject->timecreated;
            //Valid
            $transaction->allow_commit();
        } catch (Exception $e) {
            // Make sure transaction is valid.
            if (!empty($transaction) && !$transaction->is_disposed()) {
                $transaction->rollback($e);
            }
        }
    } catch (Exception $e) {
        // Silence the rollback exception or do something else.
    }

    $infoUser = [
        'id'=>$userbyid->id,
        'fullname'=>fullname($userbyid),
        'lastsentmail'=> date('d/m/Y H:i:s', $lastsentmail)
    ];
    if($flagSucess === true){
        $arrMailSucess[] = $infoUser;
    }else {
        $arrMailFailed[] = $infoUser;
    }
    
    if(count($arrMailSucess) > 0){
        $result = [
            'success' => [
                'code' => 200,
                'message' => get_string('send:success','block_elo_reminder_users'),
                'data' => [
                    'listusers' => $arrMailSucess,
                ],
                'errors' => [
                    'listusers' => $arrMailFailed,
                ]
            ]
        ];
    }
    else{
        $result = [
            'error' => [
                'code' => 405,
                'message' => get_string('send:failed','block_elo_reminder_users'),
                'errors' => [
                    'listusers' => $arrMailFailed,
                ]
            ]
        ];
    }
    return response_to_js($result);
}
