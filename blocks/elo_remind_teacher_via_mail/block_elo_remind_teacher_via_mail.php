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
 * Newblock block caps.
 *
 * @package    block_newblock
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// defined('MOODLE_INTERNAL') || die();

// require_once($CFG->dirroot . '/blocks/elo_remind_teacher_via_mail/lib.php');
use block_elo_remind_teacher_via_mail\fetcher_teacher;
require_once($CFG->dirroot . '/blocks/elo_remind_teacher_via_mail/lib.php');

class block_elo_remind_teacher_via_mail extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_elo_remind_teacher_via_mail');
    }

    function get_content() {
        global $PAGE, $OUTPUT, $CFG;
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content = new stdClass();
        $this->content->footer = '';
        $icon = $OUTPUT->pix_icon('i/course', get_string('course'));
        
        $hasupdatecourse = has_capability('block/elo_remind_teacher_via_mail:viewindashboard', context_system::instance());
        if (!$hasupdatecourse) {
            return $this->content;
        }

        // $this->page_requires($PAGE);
        
        // Lấy các users đang giảng dạy
        // $teachers = new fetcher_teacher();

        // Hiển thị bảng
        // $renderable = new  block_elo_remind_teacher_via_mail\output\main($teachers->get_teachers());
        // $renderer = $this->page->get_renderer('block_elo_remind_teacher_via_mail');

        // $this->content = (object) [
        //     'text' => $renderer->render($renderable),
        //     'footer' => ''
        // ];

        $this->content->footer = $icon."<a href=\"$CFG->wwwroot/blocks/elo_remind_teacher_via_mail/index.php\">".get_string('topage', 'block_elo_remind_teacher_via_mail')."</a> ...";   


        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => false,
                     'site-index' => false,
                     'course-view' => false, 
                     'course-view-social' => false,
                     'mod' => false, 
                     'mod-quiz' => false,
                     'my'=> true,
                    );
    }

    function has_config() {return true;}

    // function page_requires($PAGE){
    //     // Add Datatatable jquery and css
    //     $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/style.css', true);
    //     $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/datatables.min.css', true);
    //     $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/jquery.dataTables.min.css', true);
    //     $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/fixedHeader.dataTables.min.css', true);
    //     $PAGE->requires->css('/blocks/elo_remind_teacher_via_mail/css/select.dataTables.min.css', true);
    //     $PAGE->requires->js_call_amd('block_elo_remind_teacher_via_mail/init', 'init', array());
    // }
}
