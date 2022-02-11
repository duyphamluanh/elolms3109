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
 * Contains functions called by core.
 *
 * @package    block_elo_reports_log_bbb
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/grade/grade_item.php");

// Render index
function block_elo_transfer_course_grade_formula_dropdownlist_reportformat() {
    global $CFG, $DB, $PAGE;
    
    $html = '<div id="elo_transfer_course_grade_formula_advanced_search" class="elo_transfer_course_grade_formula_advanced_search">';
        $html .= '<div class="mb-3"><b>Lưu ý:</b><br>
        &nbsp;&nbsp;- Môn học gốc có cùng công thức với môn học dịch chuyển thì mới cập nhật lại id.<br>
        &nbsp;&nbsp;- Khi dịch chuyển xong sẽ hiển thị các link để kiểm tra lại các môn học tính điểm.<br>
        &nbsp;&nbsp;- Các môn học chưa có công thức thì sẽ được cập nhật công thức dựa trên môn học gốc nếu như môn học dịch chuyển có các hoạt động tính điểm như nhau.<br>
        &nbsp;&nbsp;- Các trường hợp không dịch chuyển bao gồm:<br>
        &nbsp;&nbsp;&nbsp;&nbsp;  +  Không cùng mã lớp học. VD: FINA4317 <> ACCO2402, FINA4317 <> FINA4318<br>
        &nbsp;&nbsp;&nbsp;&nbsp;  +  Cùng mã lớp học, môn dịch chuyển có công thức nhưng không trùng với môn học gốc.(Sai công thức)<br>
        &nbsp;&nbsp;&nbsp;&nbsp;  +  Cùng mã lớp học, môn học gốc và môn học dịch chuyển không có các hoạt động giống nhau.
        </div>';
        $sql = "SELECT id, fullname, shortname FROM {course} WHERE enddate > 0 AND id != 1 ORDER BY fullname ";
        $courses = $DB->get_records_sql($sql);

        // Select origin courses
        $html .= '<h2>' . get_string('origingradecoursetitle', 'block_elo_transfer_course_grade_formula') . '</h2>';
        $html .= '<select   id="orgcourseid" 
                            name="orgbercourseid" 
                            title="' . get_string('entercoursenameorshortname', 'block_elo_transfer_course_grade_formula') . '" 
                            data-placeholder="' . get_string('entercoursenameorshortname', 'block_elo_transfer_course_grade_formula') . '" 
                            class="chosen-select-tagsinput">';
        $html .= '<optgroup label="' . get_string('entercoursenameorshortname', 'block_elo_transfer_course_grade_formula') . '">';
        $html .= '<option value=""></option>';
        foreach ($courses as $course) {
            $html .= '<option value="' . $course->id . '" >' . $course->fullname . ' (' . $course->shortname . ')</option>';
        }
        $html .= '</optgroup></select>';
        $html .= '<hr class="border-0">';

        // Select transfer courses
        $html .= '<h2>' . get_string('transfergradecoursetitle', 'block_elo_transfer_course_grade_formula') . '</h2>';
        $html .= '<select   id="bercourseid" 
                            name="bercourseid" 
                            title="' . get_string('entercoursenameorshortname', 'block_elo_transfer_course_grade_formula') . '" 
                            data-placeholder="' . get_string('entercoursenameorshortname', 'block_elo_transfer_course_grade_formula') . '"multiple 
                            class="chosen-select-tagsinput">';
        $html .= '<optgroup label="' . get_string('entercoursenameorshortname', 'block_elo_transfer_course_grade_formula') . '">';
        $html .= '<option value=""></option>';
        foreach ($courses as $course) {
            $html .= '<option value="' . $course->id . '">' . $course->fullname . ' (' . $course->shortname . ')</option>';
        }
        $html .= '</optgroup></select>';
        $html .= '<hr class="border-0">';


        // Form start
        $html .= '<form class="boxcontents" action="' . $CFG->wwwroot . '/blocks/elo_transfer_course_grade_formula/transfer_formula.php" method="post" id="bercourseslmsform">';
        $html .= '<div class="boxcontents">';

            $html .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
            $html .= '<input type="hidden" name="returnto" value="' . s($PAGE->url->out(false)) . '" />';
            $html .= '<input type="checkbox" id="forceupdate" name="forceupdate">
                    <label for="forceupdate">Dịch chuyển có công thức nhưng không trùng với môn học gốc.(Sai công thức)</label><br>';
            $html .= '<div class="boxcontents buttons"><div class="boxcontents form-inline">';
            $btntransfer = get_string('transfer', 'block_elo_transfer_course_grade_formula');
            $html .= html_writer::tag('button', $btntransfer, array('id' => 'btntransfer', 'class' => 'btn btn-primary btntransfer', 'type' => 'button'));
        
        $html .= '</div></div>';

        $html .= '<input type="hidden" name="orgcourseid" value="" />';
        $html .= '<input type="hidden" name="transfercoursesid" value="" />';
        $html .= '<noscript style="display:inline">';
        $html .= '<div class="boxcontents"><input type="submit" value="' . get_string('ok') . '" /></div>';
        $html .= '</noscript>';
        $html .= '</div>';
        
        $html .= '</form>';
        // Form end

    return $html;
}

// Handling data and render transfer
function transfer_course(int $originalcourseid, array $transfercoursesid, progress_bar $progressbar, bool $forceupdate){
    
    $content = "";

    // Check course
    list($differentcourses, $noformulacourses) = check_courses_grades_formula($originalcourseid, $transfercoursesid);
    
    $notransfercourses = transfer_formula($originalcourseid,  $transfercoursesid, $noformulacourses, $progressbar, $forceupdate);
    $notransfercourses = array_merge($differentcourses,$notransfercourses);

    // Render simple report
    $content .="<div id='transferredcoursecontent'>";
    // Original course
    $content .= "<h4 class='font-weight-bold'>". get_string('orgcourse', 'block_elo_transfer_course_grade_formula')."</h4>";
    $url = new moodle_url('/grade/edit/tree/index.php?id='.$originalcourseid);
    $originalcourse = get_course($originalcourseid);
    $content .="<div class='rounded d-flex mb-1 ml-3' style='height: auto;' >
        <a  target='_blank' 
            href='$url' 
            id='notification-id-4-link'>
            ".$originalcourse->fullname."
        </a>
    </div>";

    // No formula  courses(test)
    $content .= "<h4 class='font-weight-bold'>". get_string('nonformulacourse', 'block_elo_transfer_course_grade_formula')."</h4>";
    foreach($noformulacourses as $transfercourseid){
        $url = new moodle_url('/grade/edit/tree/index.php?id='.$transfercourseid);
        $transfercourse = get_course($transfercourseid);
        $content .="<div class='rounded d-flex mb-1 ml-3' style='height: auto;' >
            <a  target='_blank' 
                href='$url' 
                id='notification-id-4-link'>
                ".$transfercourse->fullname."
            </a>
    </div>";
    }

    // Success transferred  courses
    $content .= "<h4 class='font-weight-bold'>". get_string('transferredcourse', 'block_elo_transfer_course_grade_formula')."</h4>";
    foreach($transfercoursesid as $transfercourseid){
        $url = new moodle_url('/grade/edit/tree/index.php?id='.$transfercourseid);
        $transfercourse = get_course($transfercourseid);
        $content .="<div class='rounded d-flex mb-1 ml-3' style='height: auto;' >
            <a  target='_blank' 
                href='$url' 
                id='notification-id-4-link'>
                ".$transfercourse->fullname."
            </a>
    </div>";
    }

    // Fail transferred  courses
    $content .= "<h4 class='font-weight-bold'>". get_string('nontransferredcourse', 'block_elo_transfer_course_grade_formula')."</h4>";
    foreach($notransfercourses as $transfercourseid){
        $url = new moodle_url('/grade/edit/tree/index.php?id='.$transfercourseid);
        $transfercourse = get_course($transfercourseid);
        $content .="<div class='rounded d-flex mb-1 ml-3' style='height: auto;' >
            <a  target='_blank' 
                href='$url' 
                id='notification-id-4-link'>
                ".$transfercourse->fullname."
            </a>
    </div>";
    }

    // Back to transfer page
    $gobackurl = new moodle_url('/blocks/elo_transfer_course_grade_formula/index.php');
    $content .= "<p>* Click vào các link phía trên để xem thiết lập bảng điểm</p>
        <a id='goback' class='btn btn-primary text-white' href='".$gobackurl."'>Back</a>";
    $content .="</div>";
    return $content;
}

/**
 * @param int $originalcourseid The id of course have original formulas.
 * @param array &$transfercoursesid The id of clone courses that will get formulas from original course.  
 * $transfercoursesid is passed by preference and may be changed after function end.
 * @return array
 */
function check_courses_grades_formula(int $originalcourseid, array &$transfercoursesid){
    $originalcourse = get_course($originalcourseid);
    $originalcoursecode = explode("-",$originalcourse->shortname);
    $differentcourses = [];
    $noformulacourses = [];
    foreach($transfercoursesid as $transfercourseid){
        $transfercourse = get_course($transfercourseid);
        
        if( $originalcoursecode[0] !=  explode("-",$transfercourse->shortname)[0]){
            $key = array_search($transfercourseid, $transfercoursesid);
            unset($transfercoursesid[$key]);
            $differentcourses[] = $transfercourseid;
            continue;
        }

        $formula = get_course_grade_formula($transfercourseid);
        if($formula->calculation == null){
            $key = array_search($transfercourseid, $transfercoursesid);
            unset($transfercoursesid[$key]);
            $noformulacourses[] = $transfercourseid;
        }
    }
    return array($differentcourses,$noformulacourses);
}

/**
 * @param int $originalcourseid The id of course have original formulas.
 * @param array &$transfercoursesid The ids of clone courses that will get formulas from original course.  
 * $transfercoursesid is passed by preference and may be changed after function end.
 * @param array $noformulacoursesid The id of courses dont have formula
 * @return array
 */
function transfer_formula(int $originalcourseid, array &$transfercoursesid, array $noformulacoursesid, progress_bar $progressbar, bool $forceupdate){
    global $DB;
    $progress = 0;
    $done = 0;
    $notransfercourses = [];

    $grade_items = get_course_grade_items($originalcourseid);
    $items_name = get_array_itemname($grade_items); 
    $stack = count($grade_items)*(count($transfercoursesid)+count($noformulacoursesid));  
    $progressbar->update_full($progress,"Processing...");

    // Get original course total formula
    $orgformula = get_course_grade_formula($originalcourseid);
    $orgsampleformula = denormalize_to_sample_formula( $orgformula->calculation, $originalcourseid);
    $normalorgformula = denormalize_formula($orgformula->calculation,$originalcourseid);
    

    // Get original course category formula
    $orgcategoryformulas = get_course_category_grade_formula($originalcourseid);
    if($orgcategoryformulas){
        $categoryformulas = [];
        foreach($orgcategoryformulas as $orgcategoryformula){
            $categoryformulas[] = array($orgcategoryformula->idnumber, denormalize_formula($orgcategoryformula->calculation,$originalcourseid));
        }
    }   

    // Transfer transfer courses that already have a formula
    foreach($transfercoursesid as $transfercourseid){
        $transfercourseformula = get_course_grade_formula($transfercourseid);
        $transfercoursenormalformula = denormalize_to_sample_formula($transfercourseformula->calculation, $transfercourseid);
        // Check if they have the same grade formula 
        if($orgsampleformula == $transfercoursenormalformula || $forceupdate == true){
            foreach($grade_items as $grade_item){
                $DB->set_field('grade_items', 'idnumber', $grade_item->idnumber, array('courseid' => $transfercourseid, 'itemname' => $grade_item->itemname));
                $done++;
                $progress = floor(min($done, $stack) / $stack * 100);
                $progressbar->update_full($progress,"Processing...");
            }

            if($forceupdate == true){
                // Update course  "calculation" of courses categories
                foreach($categoryformulas as $orgcategoryformula){
                    $transfercategoryformula = normalize_formula($orgcategoryformula[1],$transfercourseid);
                    $DB->set_field('grade_items', 'calculation',  $transfercategoryformula, array('courseid' =>  $transfercourseid,'idnumber' =>  $orgcategoryformula[0],'itemtype' => "category"));
                }
            
                // Update course "calculation" of courses total
                $transferformula = normalize_formula($normalorgformula,$transfercourseid);
                $DB->set_field('grade_items', 'calculation', $transferformula, array('courseid' =>  $transfercourseid, 'itemtype' => "course"));
            }
        }else {
            $key = array_search($transfercourseid, $transfercoursesid);
            unset($transfercoursesid[$key]);
            $notransfercourses[] = $transfercourseid;
        }
    }

    // Transfer transfer courses that don't have a formula
    foreach($noformulacoursesid as $noformulacourseid){
        $noformulacourse_grade_items = get_course_grade_items($noformulacourseid);
        $noformulacourse_items_name = get_array_itemname($noformulacourse_grade_items);
        if($noformulacourse_items_name == $items_name){
            // Update grade item "numberid"
            foreach($grade_items as $grade_item){
                $DB->set_field('grade_items', 'idnumber', $grade_item->idnumber, array('courseid' =>  $noformulacourseid, 'itemname' => $grade_item->itemname));
                $done++;
                $progress = floor(min($done, $stack) / $stack * 100);
                $progressbar->update_full($progress,"Processing...");
            }

            // Update course  "calculation" of courses categories
            foreach($categoryformulas as $orgcategoryformula){
                $transfercategoryformula = normalize_formula($orgcategoryformula[1],$noformulacourseid);
                $DB->set_field('grade_items', 'calculation',  $transfercategoryformula, array('courseid' =>  $noformulacourseid,'idnumber' =>  $orgcategoryformula[0],'itemtype' => "category"));
            }
           
            // Update course "calculation" of courses total
            $transferformula = normalize_formula($normalorgformula,$noformulacourseid);
            $DB->set_field('grade_items', 'calculation', $transferformula, array('courseid' =>  $noformulacourseid, 'itemtype' => "course"));
            $transfercoursesid[] = $noformulacourseid;
           
            continue;
        }
        else{
            continue;
        }
    }

    $progressbar->update_full(100,"Done!!!");
    return $notransfercourses;
}

/**
* @param array $grade_items The array of course grade items
* @return array
*/
function get_array_itemname(array $grade_items){
    $array = [];
    foreach($grade_items as $grade_item){
        $array[] = $grade_item->itemname;
    }
    return $array;
}

// Return all grade items by course id
function get_course_grade_items($courseid){
    global $DB;
    $sql = "SELECT * FROM {grade_items} WHERE courseid = $courseid AND gradetype = 1 AND itemtype = 'mod'";
    return $DB->get_records_sql($sql);
}

// Return course total formula by course id
function get_course_grade_formula($courseid){
    global $DB;
    $sql = "SELECT * FROM {grade_items} WHERE courseid = $courseid AND gradetype = 1 AND itemtype = 'course'";
    return $DB->get_record_sql($sql);
}

// Return course category formula by course id
function get_course_category_grade_formula($courseid){
    global $DB;
    $sql = "SELECT * FROM {grade_items} WHERE courseid = $courseid AND gradetype = 1 AND itemtype = 'category'";
    return $DB->get_records_sql($sql);
}

/**
 * Denormalizes the calculation formula to [idnumber] form (using for comparing)
 *
 * @param string $formula A string representation of the formula
 * @param int $courseid The course ID
 * @return string The denormalized formula as a string
 */
function denormalize_to_sample_formula($formula, $courseid) {
    if (empty($formula)) {
        return '';
    }
    // denormalize formula - convert ##giXX## to [[idnumber]]
    if (preg_match_all('/##gi(\d+)##/', $formula, $matches)) {
        foreach ($matches[1] as $id) {
            if ($grade_item = new grade_item(array('id'=>$id, 'courseid'=>$courseid))) {
                if (!empty($grade_item->idnumber)) {
                    $formula = str_replace('##gi'.$grade_item->id.'##', '[[]]', $formula);
                }
            }
        }
    }
    return $formula;
}

 /**
 * Denormalizes the calculation formula to [idnumber] form
 *
 * @param string $formula A string representation of the formula
 * @param int $courseid The course ID
 * @return string The denormalized formula as a string
 */
function denormalize_formula($formula, $courseid) {
    if (empty($formula)) {
        return '';
    }
    // denormalize formula - convert ##giXX## to [[idnumber]]
    if (preg_match_all('/##gi(\d+)##/', $formula, $matches)) {
        foreach ($matches[1] as $id) {
            if ($grade_item = new grade_item(array('id'=>$id, 'courseid'=>$courseid))) {
                if (!empty($grade_item->idnumber)) {
                    $formula = str_replace('##gi'.$grade_item->id.'##', '[['.$grade_item->idnumber.']]', $formula);
                }
            }
        }
    }
    return $formula;
}

/**
 * Denormalizes the calculation formula to [idnumber] form
 *
 * @param string $formula A string representation of the formula
 * @param int $courseid The course ID
 * @return string The denormalized formula as a string
 */
function normalize_formula($formula, $courseid) {
    $formula = trim($formula);
    if (empty($formula)) {
        return NULL;

    }
    // normalize formula - we want grade item ids ##giXXX## instead of [[idnumber]]
    if ($grade_items = grade_item::fetch_all(array('courseid'=>$courseid))) {
        foreach ($grade_items as $grade_item) {
            $formula = str_replace('[['.$grade_item->idnumber.']]', '##gi'.$grade_item->id.'##', $formula);
        }
    }
    return $formula;
}
