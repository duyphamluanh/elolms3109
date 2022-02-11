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
 * Edit course settings
 *
 * @package    core_course
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/transfer_course/lib.php');
require_once($CFG->dirroot.'/blocks/transfer_course/edit_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course id.
$categoryid = optional_param('category', 0, PARAM_INT); // Course category - can be changed in edit form.
$returnto = optional_param('returnto', 0, PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL); // A return URL. returnto must also be set to 'url'.
if ($returnto === 'url' && confirm_sesskey() && $returnurl) {
    // If returnto is 'url' then $returnurl may be used as the destination to return to after saving or cancelling.
    // Sesskey must be specified, and would be set by the form anyway.
    $returnurl = new moodle_url($returnurl);
} else {
    if (!empty($id)) {
        $returnurl = new moodle_url($CFG->wwwroot . '/blocks/transfer_course/index.php', array('categoryid' => $categoryid));
    }
    
    if ($returnto !== 0) {
        $returnurl = new moodle_url($CFG->wwwroot . '/blocks/transfer_course/index.php', array('categoryid' => $categoryid));
    }
}

// Basic access control checks.
if ($id) {
    // Editing course.
    if ($id == SITEID){
        // Don't allow editing of  'site course' using this from.
        print_error('cannoteditsiteform');
    }
//    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    // Login to the course and retrieve also all fields defined by course format.
    $course = get_course($id);
    require_login($course);
    $course = course_get_format($course)->get_course();

    $category = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
    $coursecontext = context_course::instance($course->id);
    require_capability('moodle/course:update', $coursecontext);

} else if ($categoryid) {
    // Creating new course in this category.
    $course = null;
    require_login();
    $category = $DB->get_record('course_categories', array('id'=>$categoryid), '*', MUST_EXIST);
    $catcontext = context_coursecat::instance($category->id);
    require_capability('moodle/course:create', $catcontext);
    $PAGE->set_context($catcontext);

} else {
    // Creating new course in default category.
//    $course = null;
//    require_login();
//    $category = core_course_category ::get_default();
//    $catcontext = context_coursecat::instance($category->id);
//    require_capability('moodle/course:create', $catcontext);
//    $PAGE->set_context($catcontext);
    
    $course = null;
    require_login();
    $category = $DB->get_record('course_categories', array('id'=>$categoryid), '*', MUST_EXIST);
    $catcontext = context_coursecat::instance($category->id);
    require_capability('moodle/course:create', $catcontext);
    $PAGE->set_context($catcontext);
    
}
$streditcoursesettings = get_string("edittransfercoursesettings",'block_transfer_course');
$pagedesc = $streditcoursesettings.$course->fullname.'('.$course->shortname.')';
$title = $streditcoursesettings.$course->fullname.'('.$course->shortname.')';
$fullname = $streditcoursesettings.$course->fullname.'('.$course->shortname.')';
if ($id) {
    $pageparams = array('id' => $id);
} else {
    $pageparams = array('category' => $categoryid);
}
if ($returnto !== 0) {
    $pageparams['returnto'] = $returnto;
    if ($returnto === 'url' && $returnurl) {
        $pageparams['returnurl'] = $returnurl;
    }
}
$PAGE->set_url('/blocks/transfer_course/edit.php',$pageparams);
$PAGE->set_title($title);
$PAGE->set_heading(get_string("edittransfercoursesettingstitle",'block_transfer_course'));
$PAGE->navbar->add($title);
echo $OUTPUT->header();
echo $OUTPUT->container_start('block_transfer_course');
echo $OUTPUT->heading($pagedesc,2);

// First create the form.
$args = array(
    'course' => $course,
    'category' => $category,
    'editoroptions' => $editoroptions,
    'returnto' => $returnto,
    'returnurl' => $returnurl
);

$edittransferform = new ou_transfer_course_edit_form(null, $args);  
if ($edittransferform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    redirect($returnurl);
} else if ($data = $edittransferform->get_data()) {
    // Check transfertimecourse   
    $options = true;
    if ($data->transfertimecourse == '1') {
        if (isset($CFG->block_transfer_course_getthubayvachunhatisvacation)) {
            if ( $CFG->block_transfer_course_getthubayvachunhatisvacation != true){
                $options = false;
            }
        }
        update_transfer_course($data,$options);
    }  
   
    // Set the URL to take them too if they choose save and display.
    $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
    
    if (isset($data->saveanddisplay)) {
        // Redirect user to newly created/updated course.
        redirect($courseurl);
    } else {
        // Save and return. Take them back to wherever.
        redirect($returnurl);
    }
}

$edittransferform->display();
echo $OUTPUT->container_end();
echo $OUTPUT->footer();
