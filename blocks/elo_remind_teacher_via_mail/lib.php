<?php
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->dirroot .'/mod/forum/lib.php');
// 2 weeks
const GRADING_DEADLINE = 1209600;

// Functions get data with sql
function getUserById($id){
    global $DB;
    $sql = 'SELECT * FROM {user} where id = '.$id;
    $user = $DB->get_record_sql($sql);
    return $user;
}

// Lấy những môn học chưa kết thúc dựa theo id giáo viên
function getEnrolledCourseByUserID($userid){
    global $DB;

    $sql = "SELECT c.*
    FROM {role_assignments} ra
    INNER JOIN {context} co ON co.id = ra.contextid
    INNER JOIN {course} c ON c.id = co.instanceid
    INNER JOIN {role} r ON r.id = ra.roleid
    where ra.userid = ".$userid." and c.enddate > ".time()
    ." and (r.shortname = 'teacher' or r.shortname = 'editingteacher') ";

    $courses = $DB->get_records_sql($sql);
    return $courses;
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

function getMaiLHistory(){
    global $DB;
    $sql = 'SELECT * FROM  {block_elo_remind_teacher}';
    $mailHistory = $DB->get_records_sql($sql);
    return $mailHistory;
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
            $posts = 0;
            foreach($discussions as  $discussion){
                $posts = getPostsByDisscussionID($discussion->discussion,$teacherid);
            }
            return $posts;
            break;
        case 'assign':
            return getSubmissionsByAssignmentID($activitie);
            break;
        default:
            # code...
            return 0;
            break;
    }
    return;
}   

// SEND MAIL
function elo_send_mail_to_user($touser,$fromuser){
    [$subject, $fullmessage, $messagehtml] = renderMailContent($touser,$fromuser);
    $success = email_to_user($touser, $fromuser, $subject, $fullmessage, $messagehtml);
    return $success;
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
                'message' => get_string('send:success','block_elo_remind_teacher_via_mail'),
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
                'message' => get_string('send:failed','block_elo_remind_teacher_via_mail'),
                'errors' => [
                    'listusers' => $arrMailFailed,
                ]
            ]
        ];
    }
    return response_to_js($result);
}

function page_requires($PAGE){
    // Add Datatatable jquery and css
    $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/style.css', true);
    $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/datatables.min.css', true);
    $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/jquery.dataTables.min.css', true);
    $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/fixedHeader.dataTables.min.css', true);
    $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/select.dataTables.min.css', true);
    $PAGE->requires->js_call_amd('block_elo_remind_teacher_via_mail/init', 'init', array());
}