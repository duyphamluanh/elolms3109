<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/completionlib.php');

function update_transfer_course($data,$options = NULL) {
    global $DB;
    
    $data->timemodified = time();
    // Prevent changes on front page course.
    if ($data->id == SITEID) {
        throw new moodle_exception('invalidcourse', 'error');
    }
    
    // Update with the new data
    OUHITECH_MovingAllDateTimeAvoidVacationInCourse($data);//Nhien OUHitech
    $DB->update_record('course', $data);
    // make sure the modinfo cache is reset
    rebuild_course_cache($data->id); 
}

// Nhien update_course_transfertimecourse
// Chi tinh cac ngay Le roi vao ngay T7, CN nen tru tuan Le

function OUHITECH_Is_LeTet($datetime){
   $datetext = $datetime->format('Y-m-d');
   $Tetdays = get_daysoffDate();
    if (in_array($datetext, $Tetdays)) {
        return true;
    }
   return false;
}

//DateTime
function OUHITECH_Is_Vacation($datetime) { 
    global $CFG;
    // Le Tet
    if(OUHITECH_Is_LeTet($datetime))
        return true;
    return false;
}

function OUHITECH_CountVacationDays($startdatetime, $enddatetime)
{
//    $testFormat = $startdatetime->format('Y-m-d');
    $datetime1 = date_create($startdatetime->format('Y-m-d'));
    $datetime2 = date_create($enddatetime->format('Y-m-d'));
    $interval = date_diff($datetime1, $datetime2);
    if($interval->days < 0){
        return 0;
    }
    $numvacationday = 0;
    if(OUHITECH_Is_Vacation($datetime1)){
        $numvacationday = 1;
    }
    for($dayi = 0 ; $dayi < $interval->days ; $dayi++){
        $datetime1 = $datetime1->modify('+1 day');
        if(OUHITECH_Is_Vacation($datetime1)){
            $numvacationday = $numvacationday + 1;
        }
    }
    return $numvacationday;
}

function OUHITECH_CountWorkingDays($startdatetime, $enddatetime)
{
//    $testFormat = $startdatetime->format('Y-m-d');
    $datetime1 = date_create($startdatetime->format('Y-m-d'));
    $datetime2 = date_create($enddatetime->format('Y-m-d'));
    $interval = date_diff($datetime1, $datetime2);
    if($interval->days < 0)
        return 0;
    
    $numvacationday = 0;
    if(OUHITECH_Is_Vacation($datetime1))
        $numvacationday = 1;
    for($dayi = 0 ; $dayi < $interval->days ; $dayi++)
    {
        $datetime1 = $datetime1->modify('+1 day');
        if(OUHITECH_Is_Vacation($datetime1)){
            $numvacationday = $numvacationday + 1;
        }
    }
    return $interval->days - $numvacationday;
}

function OUHITECH_MovingCourseStartDateAvoidVacation($startnewdatetime)
{
    while(OUHITECH_Is_Vacation($startnewdatetime))
    {
        //$startnewdatetime = new DateTime(date('Y-m-d', $startnewdatetime));
        $startnewdatetime =  $startnewdatetime->modify('+1 day');
      //  $startnewdatetime = (new \DateTimeImmutable())->setTimestamp($startnewdatetime)->modify("+1 days");
    }
    return $startnewdatetime;
}
function OUHITECH_MovingCourseStartTimestampAvoidVacation($startnewtimestamp)
{
    $textDateTime = date('Y-m-d H:i:s',$startnewtimestamp);
    $startnewdatetime = new DateTime($textDateTime);
    $ResultDateTime = OUHITECH_MovingCourseStartDateAvoidVacation($startnewdatetime);
    return $ResultDateTime->getTimestamp();
}
function OUHITECH_MovingDateTimeAvoidVacation($CoureOldStartDatetime, $OldDatetimeInOldCourse, $CoureNewStartDatetime)
{
    //$CoureOldEndDatetime = $CoureOldEndDatetime->modify('+90 day');// Ma vi du
    $CountWorkingDays = OUHITECH_CountWorkingDays($CoureOldStartDatetime, $OldDatetimeInOldCourse);
    
    $NewDatetimeInNewCourse = clone($CoureNewStartDatetime);
    $NewDatetimeInNewCourse->modify('+' .$CountWorkingDays.'day');
    $CountVacationDays = OUHITECH_CountVacationDays($CoureNewStartDatetime,$NewDatetimeInNewCourse);
    While ($CountVacationDays > 0)
    {
        $NewDatetimeInNewCourse->modify('+1 day');
        if(OUHITECH_Is_Vacation($NewDatetimeInNewCourse)==false)
        {
            $CountVacationDays = $CountVacationDays - 1;
        }
    }
    $NewTimeStampInNewCourse = $NewDatetimeInNewCourse->getTimestamp();
    $OldTimeStampInOldCourse = $OldDatetimeInOldCourse->getTimestamp();
    $textDate = date('Y-m-d',$NewTimeStampInNewCourse);
    $textTime = date(' H:i:s',$OldTimeStampInOldCourse);
    $NewDatetimeInNewCourse = new DateTime($textDate. $textTime);
    
//    $textDate = date('Y-m-d',$NewTimeStampInNewCourse);
//    $textTime = date(' H:i:s',$OldDatetimeInOldCourse);
//    $NewDatetimeInNewCourse = new DateTime($textDate + $textTime);
    return $NewDatetimeInNewCourse;
}

function OUHITECH_MovingTimeStampAvoidVacation($CoureOldStartTimeStamp, $OldTimeStampInOldCourse, $CoureNewStartTimeStamp)
{
    $textDateTime = date('Y-m-d H:i:s',$CoureOldStartTimeStamp);
    $CoureOldStartDatetime = new DateTime($textDateTime);
    $textDateTime = date('Y-m-d H:i:s',$OldTimeStampInOldCourse);
    $OldDatetimeInOldCourse = new DateTime($textDateTime);
    $textDateTime = date('Y-m-d H:i:s',$CoureNewStartTimeStamp);
    $CoureNewStartDatetime = new DateTime($textDateTime);
    $DateTimeResult = OUHITECH_MovingDateTimeAvoidVacation($CoureOldStartDatetime, $OldDatetimeInOldCourse, $CoureNewStartDatetime);
    $TimestampResult = $DateTimeResult->getTimestamp();
    return $TimestampResult;
}

function OUHITECH_MoveTimeInJson($jsontext,$CoureOldStarttimestamp,$CoureNewStarttimestamp)
{
    $JsonStruct = json_decode($jsontext);
    if($JsonStruct->c)
    {
        foreach ($JsonStruct->c as $key => $datavar) {
            if(($datavar->type== "date") && ($datavar->t > 0))
            {
                $datavar->t = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, $datavar->t, $CoureNewStarttimestamp);
            }
        }
    }
    $newjsontext = json_encode($JsonStruct);
    //if lay duoc timstmaple in $availability
    //move do xong dan lai
    //string	"{"op":"&","c":[{"type":"completion","cm":52437,"e":0},{"type":"date","d":">=","t":1544979600}],"showc":[true,true]}"    
    return $newjsontext;
}

function OUHITECH_MoveTimeInHtml($htmltext,$OldStarttimestamp,$NewStarttimestamp)
{
    $textDateNew = date('d/m/Y',$NewStarttimestamp);
    $WeekDayNumNew =  intval(date('w',$NewStarttimestamp));
    $textDateOld = date('d/m/Y',$OldStarttimestamp);
    $WeekDayNum =  intval(date('w',$OldStarttimestamp));
    $ThuNgay = array("Chủ nhật","Thứ hai","Thứ ba","Thứ tư","Thứ năm","Thứ sáu","Thứ bảy");
    $WeekDay = $ThuNgay[$WeekDayNum];
    $WeekDayNew = $ThuNgay[$WeekDayNumNew];
//    $HtmlStruct = html_entity_decode($htmltext);
    ///Provides: You should eat pizza, beer, and ice cream every day
    //$phrase  = "You should eat fruits, vegetables, and fiber every day.";
    $OldDays = array($WeekDay, $textDateOld);
    $NewDays = array($WeekDayNew, $textDateNew);
    //$yummy   = array("pizza", "beer", "ice cream");

    $newhtmltext = str_replace($OldDays,$NewDays,$htmltext);

    //$newhtmltext = html_entity_encode($HtmlStruct);
    return $newhtmltext;
}

function OUHITECH_MoveTimeForum($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        $assesstimestart_old = $data->assesstimestart;
        $assesstimefinish_old = $data->assesstimefinish;
        
        if($data->assesstimestart > 0){
            $data->assesstimestart = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, $data->assesstimestart, $CoureNewStarttimestamp);
        }
        
        if($data->assesstimefinish > 0){
            $data->assesstimefinish = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, $data->assesstimefinish, $CoureNewStarttimestamp);
        }
        
        if($data->intro)
        {
            if($assesstimestart_old > 0){
                $data->intro = OUHITECH_MoveTimeInHtml($data->intro,$assesstimestart_old,$data->assesstimestart); 
            }
            if($assesstimefinish_old > 0){
                $data->intro = OUHITECH_MoveTimeInHtml($data->intro,$assesstimefinish_old,$data->assesstimefinish);
            }
        }
        
//        if ($data->assesstimestart > 0 && $data->assesstimefinish > 0){
//            $moddata = [
//                'id' => $act->instance,
//                'timemodified' => time(),
//                'assesstimestart'=> $data->assesstimestart,
//                'assesstimefinish'=> $data->assesstimefinish,
//                'intro'=> $data->intro
//            ];
//            $DB->update_record($act->modname, $moddata);
//        }
        $moddata = [
                'id' => $act->instance,
                'timemodified' => time(),
                'assesstimestart'=> $data->assesstimestart,
                'assesstimefinish'=> $data->assesstimefinish,
                'intro'=> $data->intro
            ];
        $DB->update_record($act->modname, $moddata);
        
        
        // 2020_11_01_Restricted
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            if($act->completionexpected > 0) {
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                    'id' => $act->id,
                    'availability' => $OU_availability,
                    'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                //update event schedule
                //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                \core_completion\api::update_completion_date_event($act->id, 'forum', $act->instance, $OU_completionexpected);
            }   
        }
        
        // 2020_11_01_No Restricted
        if($act->completionexpected > 0){
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                        'id' => $act->id,
                        'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                    //update event schedule
                    //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                 \core_completion\api::update_completion_date_event($act->id, 'forum', $act->instance, $OU_completionexpected);
        }
}

function OUHITECH_MoveTimeQuiz($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        if(intval($data->timeopen) > 0){
            $data->timeopen = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeopen), $CoureNewStarttimestamp);
        }
        if(intval($data->timeclose) > 0){
            $data->timeclose = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeclose), $CoureNewStarttimestamp);
        }
        $moddata = [
            'id' => $act->instance,
            'timemodified' => time(),
            'timeopen'=> $data->timeopen,
            'timeclose'=> $data->timeclose
                ];
        $DB->update_record($act->modname, $moddata);
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        }
        //update event schedule
        OUHITECH_UpdateQuizEventsSchedule($act->instance, $data); //mod/quiz/lib.php
}

function OUHITECH_UpdateQuizEventsSchedule($activity,$quiz) {
    global $DB;
    // Load the old events relating to this quiz.
    $conds = array('modulename'=>'quiz',
                   'instance'=>$activity);
    $oldevents = $DB->get_records('event', $conds, 'id ASC');
    //Check lịch trình cũ có chưa, nếu có rồi thì xóa đi và tạo lịch trình mới với ngày kết thúc mới
    foreach ($oldevents as $oldevent) {
        if (($oldevent->timestart !== $quiz->timeclose)) {	
            $DB->delete_records('event',array('id' => $oldevent->id));
        }
    }
    quiz_update_events($quiz);
}

function OUHITECH_MoveTimeAssign($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        
        if(intval($data->duedate) > 0){
            $data->duedate = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->duedate), $CoureNewStarttimestamp);
        }

        if(intval($data->cutoffdate) > 0){
            $data->cutoffdate = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->cutoffdate), $CoureNewStarttimestamp);
        }
        
        if(intval($data->gradingduedate) > 0){
            $data->gradingduedate = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->gradingduedate), $CoureNewStarttimestamp);
        }

        $moddata = [
            'id' => $act->instance, 
            'timemodified' => time(),
            'duedate'=> $data->duedate,
            'cutoffdate'=> $data->cutoffdate,
            'gradingduedate'=> $data->gradingduedate,
        ];
        
        $DB->update_record($act->modname, $moddata);
        
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        } 
        //update event schedule
        OUHITECH_UpdateAssignEventsSchedule($data, $act); //mod/assign/lib.php

        // 2020_11_01_Restricted
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            if($act->completionexpected > 0){
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                    'id' => $act->id,
                    'availability' => $OU_availability,
                    'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                //update event schedule
                //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                \core_completion\api::update_completion_date_event($act->id, 'assign', $act->instance, $OU_completionexpected);
            }   
        }
        
        // 2020_11_01_No Restricted
        if($act->completionexpected > 0){
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                        'id' => $act->id,
                        'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                    //update event schedule
                    //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                 \core_completion\api::update_completion_date_event($act->id, 'assign', $act->instance, $OU_completionexpected);
        }
        
}

function OUHITECH_UpdateAssignEventsSchedule($instance,$activity){
    global $DB, $CFG;
    require_once($CFG->dirroot.'/calendar/lib.php');
    define('ASSIGN_EVENT_TYPE_DUE', 'due');
    define('ASSIGN_EVENT_TYPE_GRADINGDUE', 'gradingdue');
    define('CALENDAR_EVENT_TYPE_ACTION', 1);
    $eventtype = ASSIGN_EVENT_TYPE_DUE;
    if ($instance->duedate) {
        $event->name = get_string('calendardue', 'assign', $instance->name);
        $intro = $instance->intro;
        $event->description = array(
            'text' => $intro,
            'format' => $instance->introformat
        );

        $event->eventtype = $eventtype;
        $event->timestart = $instance->duedate;
        $event->timesort = $instance->duedate;
        $event->courseid = $instance->course;
        $event->modulename = $activity->modname;
        $event->groupid = 0;
        $event->instance = $instance->id;
        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        $select = "modulename = :modulename
                   AND instance = :instance
                   AND eventtype = :eventtype
                   AND groupid = 0
                   AND courseid <> 0";
        $params = array('modulename' => 'assign', 'instance' => $instance->id, 'eventtype' => $eventtype);
        $event->id = $DB->get_field_select('event', 'id', $select, $params);
        // Now process the event.
        if ($event->id) {
            $calendarevent= calendar_event::load($event->id); 
            $calendarevent->update($event);
        }
        else {
            calendar_event::create($event); // Tạo sự kiện nếu chưa có
        }

    }else {
        $DB->delete_records('event', array('modulename' => 'assign', 'instance' => $instance->id,
            'eventtype' => $eventtype));
    }
    $eventtype = ASSIGN_EVENT_TYPE_GRADINGDUE;
    if ($instance->gradingduedate) {
        $event->name = get_string('calendargradingdue', 'assign', $instance->name);
        $intro = $instance->intro;
        $event->description = array(
            'text' => $intro,
            'format' => $instance->introformat
        );
        $event->eventtype = $eventtype;
        $event->timestart = $instance->gradingduedate;
        $event->timesort = $instance->gradingduedate;
        $event->courseid = $instance->course;
        $event->modulename = $activity->modname;
        $event->groupid = 0;
        $event->instance = $instance->id;
        $event->id = $DB->get_field('event', 'id', array('modulename' => 'assign',
            'instance' => $instance->id, 'eventtype' => $event->eventtype));

        // Now process the event.
        if ($event->id) {
            $calendarevent= calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            calendar_event::create($event);
        }
    } else {
        $DB->delete_records('event', array('modulename' => 'assign', 'instance' => $instance->id,
        'eventtype' => $eventtype));
    }
}

function OUHITECH_MoveTimeScorm($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB,$CFG;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        if(intval($data->timeopen) > 0){
            $data->timeopen = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeopen), $CoureNewStarttimestamp);
        }
        if(intval($data->timeclose) > 0){
            $data->timeclose = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeclose), $CoureNewStarttimestamp);
        }
        if (intval($data->timeopen) > 0 && intval($data->timeclose) > 0) {
            $moddata = [
                    'id' => $act->instance,
                    'timemodified' => time(),
                    'timeopen'=> $data->timeopen,
                    'timeclose'=> $data->timeclose
                   ];
            $DB->update_record($act->modname, $moddata);
        }
        if (intval($data->timeopen) == 0 && intval($data->timeclose) > 0) {
            $moddata = [
                    'id' => $act->instance,
                    'timemodified' => time(),
                    'timeclose'=> $data->timeclose
                   ];
            $DB->update_record($act->modname, $moddata);  
        }
        
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        } 
        require_once("$CFG->dirroot/mod/scorm/locallib.php");
        scorm_update_calendar($data, $act->id);
}

function OUHITECH_MoveTimeGeneral($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
     global $DB;
//        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
//        if(intval($data->timeopen) > 0){
//            $data->timeopen = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeopen), $CoureNewStarttimestamp);
//        }
//        if(intval($data->timeclose) > 0){
//            $data->timeclose = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeclose), $CoureNewStarttimestamp);
//        }
//        if (intval($data->timeopen) == 0 && intval($data->timeclose) > 0) {
//            $moddata = [
//                'id' => $act->instance,
//                'timemodified' => time(),
//                'timeopen'=> $data->timeopen,
//                'timeclose'=> $data->timeclose
//            ];
//            $DB->update_record($act->modname, $moddata);
//        }
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        }        
}

function OUHITECH_MovingAllDateTimeAvoidVacationInCourse($data){	
//    global $DB;
    if($data->transfertimecourse != '1'){
        return;
    }
    $oldcourse = course_get_format($data->id)->get_course();
    $CoureNewStarttimestamp = OUHITECH_MovingCourseStartTimestampAvoidVacation($data->startdate);
    $CoureNewEndtimestamp = OUHITECH_MovingTimeStampAvoidVacation($oldcourse->startdate, $oldcourse->enddate, $CoureNewStarttimestamp);
    $data->startdate = $CoureNewStarttimestamp;
    $data->enddate = $CoureNewEndtimestamp;
/*    if ($data->startdate != $oldcourse->startdate && $data->enddate != $oldcourse->enddate) {
        
        $coursedata = ['id' => $data->id, 'startdate' => $CoureNewStarttimestamp, 'enddate'=> $CoureNewEndtimestamp];
        //$coursedata = ['id' => $data->id, 'startdate' => $CoureNewStartDatetime, 'enddate'=> $data->enddate];
        $DB->update_record('course', $coursedata);
    }
*/    
    //get activities
    $moduleinfo = get_fast_modinfo($data->id);
    $cmss = $moduleinfo->get_cms();
    $activities = array();
    $acceptact = array('label','bigbluebuttonbn','page');
    foreach ($cmss as $key => $cms) {
//        $mod = $moduleinfo->get_cm($key);
        if (in_array($cms->modname, $acceptact)) {
            continue;
        }
        if (!$cms->uservisible) {
            continue;
        }
        if ($cms->completion == COMPLETION_TRACKING_NONE) {//Bỏ các hoạt động không có completion
            continue;
        }
        if ($cms->modname == 'resource' && !isset($cms->availabitity)) {//Cac resource khong co dieu kien
            continue;
        }
        $activities[$key]= $cms;
    }
    // Luc Loi All Date Time Trong Course tu data base ke ca Date Ket Thuc cua $iCoureOldDatetime
    foreach ($activities as $key => $act) {
        if($act->modname == 'forum'){
            OUHITECH_MoveTimeForum($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }
        else if($act->modname == 'quiz'){
            OUHITECH_MoveTimeQuiz($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }  
        else if($act->modname == 'assign'){
            OUHITECH_MoveTimeAssign($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }  
        else if($act->modname == 'scorm'){
            OUHITECH_MoveTimeScorm($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }  
        else {
            OUHITECH_MoveTimeGeneral($act,$oldcourse->startdate,$CoureNewStarttimestamp);//resource
        }
    }
}

// Elo: Duy update 
function get_daysoffDate(){
    global $DB;
    $sql = "SELECT d.date FROM {transfer_course_dayoff} d";
    $result = $DB->get_records_sql($sql);
    $array = [];
    foreach($result as $date){
        array_push($array, date('Y-m-d',$date->date));   
    }
    return $array;
}

function check_daysoff(){
    $daysoff = get_daysoff();
    renderDaysoffTable($daysoff);
}

function get_daysoff(){
    global $DB;
    $sql = "SELECT * FROM {transfer_course_dayoff}";
    return $DB->get_records_sql($sql);
}

function renderDaysoffTable($daysoff){
    $table = new html_table();
    $table->id = "generaltable";
    $table->head = array(get_string("date",'block_transfer_course'),
                        get_string("event",'block_transfer_course'),
                        get_string("schoolyear",'block_transfer_course'),
                        get_string("semester",'block_transfer_course'),
                        ''
                        );
    foreach($daysoff as $dayoff){
        $btnDelete = "<button id='removeDayOff_".$dayoff->date."' class='btn btn-primary' value='".$dayoff->date."'>Remove</button>";
        $table->data[] = array(date('Y-m-d',$dayoff->date), 
                            $dayoff->event, 
                            $dayoff->year, 
                            $dayoff->semester,
                            $btnDelete
                        );
    }
    echo html_writer::table($table);
}

function renderDaysoffFromFileTable($daysoff){

    foreach($daysoff as $key=>$dayoff) {
        if(substr( $dayoff, 0, 2 ) === '//' || $dayoff == "") {
            unset($daysoff[$key]);
        }else{
            $daysoff[$key] = explode(',',$dayoff);
        }
    }
    $table = new html_table();
    $table->id = "daysofffromfiletable";
    $table->head = array(get_string("date",'block_transfer_course'),
                            get_string("semester",'block_transfer_course'),
                            get_string("schoolyear",'block_transfer_course'),
                            get_string("event",'block_transfer_course')
                        );
    foreach($daysoff as $dayoff){
        $table->data[] = array(
            trim($dayoff[0]),
            trim($dayoff[1]),
            trim($dayoff[2]),
            trim($dayoff[3]));
    }
    echo html_writer::table($table);
    echo html_writer::start_tag('div', array('class' => 'pb-5'));
    $attributes = ['class' => 'btn btn-primary float-right', 'id' => 'addAllDaysOff'];
    echo html_writer::tag('button', 'Add all', $attributes);
    echo html_writer::end_tag('div');
}

function addApiLink(){
    $addDayOffApiUrl = new moodle_url('/blocks/transfer_course/apiAddDayOff.php');
    $deleteDayOffApiUrl = new moodle_url('/blocks/transfer_course/apiDeleteDayOff.php');
    $html = '<input id="sesskey" type="hidden" name="sesskey" value="' . sesskey() . '" />
            <input id="addDayOffApiUrl" type="hidden" name="addDayOffApiUrl" value="' . $addDayOffApiUrl . '" />
            <input id="deleteDayOffApiUrl" type="hidden" name="deleteDayOffApiUrl" value="' . $deleteDayOffApiUrl . '" />';
    echo $html;
}

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
class add_dayoff_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('header', 'dayoff_headerAdd', get_string("adddaysoff",'block_transfer_course'));

        $mform->addElement('date_selector', 'dayoff_date', get_string("date",'block_transfer_course'));

        $attributes=array('size'=>'30');
        $mform->addElement('text', 'dayoff_event', get_string("event",'block_transfer_course'), $attributes);
        $attributes=array('size'=>'30', 'placeholder' => 'VD: 2020-2021');
        $mform->addElement('text', 'dayoff_schoolyear', get_string("schoolyear",'block_transfer_course'), $attributes);
        
        $mform->addElement('select', 'dayoff_semester', get_string("semester",'block_transfer_course'), array('1', '2', '3'));
        
        $mform->addElement('button', 'dayoff_add', 'Add');
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

class upload_dayoff_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $maxbytes = 20;
        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('header', 'dayoff_headerUpload', get_string("uploaddaysoff",'block_transfer_course'));
        $mform->addElement('filepicker', 'userfile', get_string('file'), null,
                   array('maxbytes' => $maxbytes, 'accepted_types' => array('.txt')));
        $mform->addRule('userfile', get_string('required'), 'required');
        $this->add_action_buttons(false,get_string("getdata",'block_transfer_course')); 
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

function readjson($file){
    $str = file_get_contents($file);
    $json = json_decode($str, true);
    return $json;
}

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);
    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}
// Elo: Duy update end