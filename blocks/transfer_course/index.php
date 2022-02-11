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
 * Course and category management interfaces.
 *
 * @package    core_course
 * @copyright  2013 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
// require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/blocks/transfer_course/lib.php');
$categoryid = optional_param('categoryid', null, PARAM_INT);
$selectedcategoryid = optional_param('selectedcategoryid', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', null, PARAM_INT);
$viewmode = optional_param('view', 'courses', PARAM_ALPHA); // Can be one of default, combined, courses, or categories.

// Search related params.
$search = optional_param('search', '', PARAM_RAW); // Search words. Shortname, fullname, idnumber and summary get searched.
$blocklist = optional_param('blocklist', 0, PARAM_INT); // Find courses containing this block.
$modulelist = optional_param('modulelist', '', PARAM_PLUGIN); // Find courses containing the given modules.

if (!in_array($viewmode, array('default', 'combined', 'courses', 'categories'))) {
    $viewmode = 'courses';
}

$issearching = ($search !== '' || $blocklist !== 0 || $modulelist !== '');
if ($issearching) {
    $viewmode = 'courses';
}

$url = new moodle_url('/blocks/transfer_course/index.php');
$systemcontext = $context = context_system::instance();


if ($courseid) {
    $record = get_course($courseid);
    $course = new core_course_list_element($record);
    $category = core_course_category ::get($course->category);
    $categoryid = $category->id;
    $context = context_coursecat::instance($category->id);
    $url->param('categoryid', $categoryid);
    $url->param('courseid', $course->id);
    $displaycoursedetail = (isset($courseid));

} else if ($categoryid) {
    $courseid = null;
    $course = null;
    $category = core_course_category ::get($categoryid);
    $context = context_coursecat::instance($category->id);
    $url->param('categoryid', $category->id);

} else {
    $course = null;
    $courseid = null;
    $category = core_course_category ::get_default();
    $categoryid = $category->id;
    $context = context_coursecat::instance($category->id);
    $url->param('categoryid', $category->id);
}

// Check if there is a selected category param, and if there is apply it.
if ($course === null && $selectedcategoryid !== null && $selectedcategoryid !== $categoryid) {
    $url->param('categoryid', $selectedcategoryid);
}

if ($page !== 0) {
    $url->param('page', $page);
}
if ($viewmode !== 'default') {
    $url->param('view', $viewmode);
}
if ($search !== '') {
    $url->param('search', $search);
}
if ($blocklist !== 0) {
    $url->param('blocklist', $search);
}
if ($modulelist !== '') {
    $url->param('modulelist', $search);
}

//Must import js css 
$PAGE->requires->css('/blocks/transfer_course/css/datatables.min.css', true);
$PAGE->requires->css('/blocks/transfer_course/css/jquery.dataTables.min.css', true);
$PAGE->requires->css('/blocks/transfer_course/css/fixedHeader.dataTables.min.css', true);
$PAGE->requires->css('/blocks/transfer_course/css/select.dataTables.min.css', true);

$pageheading = get_string('pluginname', 'block_transfer_course');
$title = get_string('ou:listcourses', 'block_transfer_course');
$PAGE->set_context($context);
$PAGE->set_url($url);
//$PAGE->set_pagelayout('base');
$PAGE->set_title($pageheading);
$PAGE->set_heading($pageheading);
$PAGE->navbar->add($title);

// This is a system level page that operates on other contexts.
require_login();

$notificationspass = array();
$notificationsfail = array();

if (!is_null($perpage)) {
    set_user_preference('coursecat_management_perpage', $perpage);
} else {
    $perpage = get_user_preferences('coursecat_management_perpage', $CFG->coursesperpage);
}
if ((int)$perpage != $perpage || $perpage < 2) {
    $perpage = $CFG->coursesperpage;
}

$categorysize = 4;
$coursesize = 4;
$detailssize = 4;

if ($viewmode === 'courses') {
    if (isset($courseid)) {
        $coursesize = 6;
        $detailssize = 6;
        $class = 'columns-2';
    } else {
        $coursesize = 12;
        $class = 'columns-1';
    }
}
if ($viewmode === 'default' || $viewmode === 'combined') {
    $class .= ' viewmode-cobmined';
} else {
    $class .= ' viewmode-'.$viewmode;
}
if (($viewmode === 'default' || $viewmode === 'combined' || $viewmode === 'courses') && !empty($courseid)) {
    $class .= ' course-selected';
}

/* @var core_course_management_renderer|core_renderer $renderer */
$renderer = $PAGE->get_renderer('block_transfer_course');
$renderer->enhance_management_interface();
$displaycourselisting = true;
$displaycategorylisting= false;
$displaycoursedetail = (isset($courseid));
echo $renderer->header();

if (!$issearching) {
    echo $renderer->management_heading($title, $viewmode, $categoryid);
} else {
    echo $renderer->management_heading(new lang_string('searchresults'));
}

if (count($notificationspass) > 0) {
    echo $renderer->notification(join('<br />', $notificationspass), 'notifysuccess');
}
if (count($notificationsfail) > 0) {
    echo $renderer->notification(join('<br />', $notificationsfail));
}
echo $renderer->elo_transfer_course_search_form($search);

// Start the management form.
echo $renderer->management_form_start();

echo $renderer->accessible_skipto_links($displaycategorylisting, $displaycourselisting, $displaycoursedetail);

echo $renderer->grid_start('course-category-listings', $class);

if ($displaycourselisting) {
    echo $renderer->grid_column_start($coursesize, 'course-listing');
    if (!$issearching) {
        echo $renderer->course_listing($category, $course, $page, $perpage, $viewmode);
    } else {
        list($courses, $coursescount, $coursestotal) =
            \core_course\management\helper::search_courses($search, $blocklist, $modulelist, $page, $perpage);
        echo $renderer->search_listing($courses, $coursestotal, $course, $page, $perpage, $search);
    }
    echo $renderer->grid_column_end();
    if ($displaycoursedetail) {
        echo $renderer->grid_column_start($detailssize, 'course-detail');
        echo $renderer->course_detail($course);
        echo $renderer->grid_column_end();
    }
}
echo $renderer->grid_end(); 

// Duy Update
$mform2 = new upload_dayoff_form();
//Form processing and displaying is done here
if($fromform = $mform2->get_data()) {
    $mform2->set_data($toform);
    $mform2->display();
    $array = array_map('trim', preg_split('/\r\n|\r|\n/', $mform2->get_file_content('userfile')));
    renderDaysoffFromFileTable($array);
} else {
  //Set default data (if any)
    $mform2->set_data($toform);
  //displays the form
    $mform2->display();
}

$mform1 = new add_dayoff_form();
$mform1->set_data($toform);
$mform1->display();

addApiLink();
check_daysoff();

// Duy Update end
$PAGE->requires->js_call_amd('block_transfer_course/init', 'init', array());

// End of the management form.
echo $renderer->management_form_end();

echo $renderer->footer();
