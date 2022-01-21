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
 * Defines renderer for course format eloflexsections
 *
 * @package    format_eloflexsections
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/format/renderer.php');

/**
 * Renderer for eloflexsections format.
 *
 * @copyright 2012 Marina Glancy
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_eloflexsections_renderer extends plugin_renderer_base {
    /** @var core_course_renderer Stores instances of core_course_renderer */
    protected $courserenderer = null;

    /**
     * Constructor
     *
     * @param moodle_page $page
     * @param type $target
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courserenderer = $page->get_renderer('core', 'course');
    }

    /**
     * Generate the section title (with link if section is collapsed)
     *
     * @param int|section_info $section
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course, $supresslink = false) {
        global $CFG;
        if ((float)$CFG->version >= 2016052300) {
            // For Moodle 3.1 or later use inplace editable for displaying section name.
            $section = course_get_format($course)->get_section($section);
            return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, $supresslink));
        }
        $title = get_section_name($course, $section);
        if (!$supresslink) {
            $url = course_get_url($course, $section, array('navigation' => true));
            if ($url) {
                $title = html_writer::link($url, $title);
            }
        }
        return $title;
    }

    /**
     * Generate html for a section summary text
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function format_summary_text($section) {
        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);

        $options = new stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;
        return format_text($summarytext, $section->summaryformat, $options);
    }
    //Nhien elo_echo_start_section
    public function elo_echo_start_section($eloclassnametail, $title,$elosummary,$elocontrolsstr,$sectioncollapsed,$level) {
        $level = MIN($level,5);

        $title = '<span id="'.$eloclassnametail.'" class = "elosectiontitle-' . $level. '">'. $title . '</span>';
        $elosummary = '<div class = "elosectionsummary-' . $level. '">'. $elosummary . '</div>';

        $checked = '';
        if($sectioncollapsed == FORMAT_ELOFLEXSECTIONS_EXPANDED){
            $checked = 'show';            
        }
        global $elo_echo_start_section;
        $eloclassnametail .= $elo_echo_start_section;
        if($elo_echo_start_section){
            echo '<div class="elo_collapsetitle collapsed sectiontoggle" data-toggle="collapse"  href="#collapse'.$eloclassnametail.'">
            <label for="collapsible' . $eloclassnametail . '" class="lbl-toggle">' . $title . $elocontrolsstr .$elosummary . '</label></div>
            <div id="collapse'.$eloclassnametail.'" class="panel-collapse collapse '.$checked.'">
            <div class="content-inner">';
        }
        else {
            echo '<div><div><div>';
        }
        $elo_echo_start_section ++;
    }
    public function elo_echo_end_section() {
        echo '</div></div>';
    } 
            
            
    /**
     * Display section and all its activities and subsections (called recursively)
     *
     * @param int|stdClass $course
     * @param int|section_info $section
     * @param int $sr section to return to (for building links)
     * @param int $level nested level on the page (in case of 0 also displays additional start/end html code)
     */
public function elo_course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->courserenderer->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->courserenderer->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                            html_writer::link($movingurl, $this->courserenderer->output->render($movingpix), array('title' => $strmovefull)),
                            array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->courserenderer->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
            }
        }

        // Always output the section module list.
        if($sectionoutput != ''){ // Nhien Only change here not display empty summary
            $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text'));
        }

        return $output;
    }
    public function display_section($course, $section, $sr, $level = 0) {
        global $PAGE;
        $course = course_get_format($course)->get_course();
        $section = course_get_format($course)->get_section($section);
        $context = context_course::instance($course->id);
        $contentvisible = true;
        if (!$section->uservisible || !course_get_format($course)->is_section_real_available($section)) {
            if ($section->visible && !$section->available && $section->availableinfo) {
                // Still display section but without content.
                $contentvisible = false;
            } else {
                return '';
            }
        }
        $sectionnum = $section->section;
        $movingsection = course_get_format($course)->is_moving_section();
        if ($level === 0) {
            $cancelmovingcontrols = course_get_format($course)->get_edit_controls_cancelmoving();
            foreach ($cancelmovingcontrols as $control) {
                echo $this->render($control);
            }
            echo html_writer::start_tag('ul', array('class' => 'eloflexsections eloflexsections-level-0'));
            if ($section->section) {
                $this->display_insert_section_here($course, $section->parent, $section->section, $sr);
            }
        }
        
        // display controls except for expanded/collapsed
        $controls = course_get_format($course)->get_section_edit_controls($section, $sr);
        $collapsedcontrol = null;
        $controlsstr = '';
        $elocontrolsstr = '';
        $elosummary = '';
        foreach ($controls as $idxcontrol => $control) {
            if ($control->class === 'expanded' || $control->class === 'collapsed') {
                $collapsedcontrol = $control;
            } else {
                $controlsstr .= $this->render($control);
            }
        }
        if (!empty($controlsstr)) {
            $elocontrolsstr = html_writer::tag('div', $controlsstr, array('class' => 'controls'));
        }
        
        $elotile = $this->section_title($sectionnum, $course, ($level == 0));         
        if ($contentvisible && ($summary = $this->format_summary_text($section))){
            $elosummary = html_writer::tag('div', $summary, array('class' => 'summary'));
        }
         // Nhien Bo vao ben ngoai cho dep
        $this->elo_echo_start_section('section' . $sectionnum,$elotile,$elosummary,$elocontrolsstr,$section->collapsed,$level);
        echo html_writer::start_tag('li',
                array('class' => "section main".
                    ($movingsection === $sectionnum ? ' ismoving' : '').
                    (course_get_format($course)->is_section_current($section) ? ' current' : '').
                    (($section->visible && $contentvisible) ? '' : ' hidden'),
                    'id' => 'section-'.$sectionnum));//Nhien elo fix hien thi dung vi tri khi click

        // display section content
        echo html_writer::start_tag('div', array('class' => 'content'));
        // display section name and expanded/collapsed control
        if ($sectionnum && ($title = $this->section_title($sectionnum, $course, ($level == 0) || !$contentvisible))) {
            if ($collapsedcontrol) {
                $title = $this->render($collapsedcontrol). $title;
            }
         //   echo html_writer::tag('h3', $title, array('class' => 'sectionname'));
        }

        echo $this->section_availability_message($section,
            has_capability('moodle/course:viewhiddensections', $context));

        // display section description (if needed)
        if ($contentvisible && ($summary = $this->format_summary_text($section))) {
            //echo html_writer::tag('div', $summary, array('class' => 'summary')); // Nhien Bo vao ben ngaoi cho dep
        } else {
            echo html_writer::tag('div', '', array('class' => 'summary nosummary'));
        }
        // display section contents (activities and subsections)
        //Nhien 
        if ($contentvisible || ($level == 0)) {
        //if ($contentvisible && ($section->collapsed == FORMAT_ELOFLEXSECTIONS_EXPANDED || !$level)) {
            // display resources and activities
            echo $this->elo_course_section_cm_list($course, $section, $sr);
            if ($PAGE->user_is_editing()) {
                // a little hack to allow use drag&drop for moving activities if the section is empty
                if (empty(get_fast_modinfo($course)->sections[$sectionnum])) {
                    echo "<ul class=\"section img-text\">\n</ul>\n";
                }
                echo $this->courserenderer->course_section_add_cm_control($course, $sectionnum, $sr);
            }
            // display subsections
            $children = course_get_format($course)->get_subsections($sectionnum);
            if (!empty($children) || $movingsection) {
                //$this->elo_echo_start_section('-level-' . ($level+1));
                echo html_writer::start_tag('ul', array('class' => 'eloflexsections eloflexsections-level-'.($level+1)));
               
                foreach ($children as $num) {
                    $this->display_insert_section_here($course, $section, $num, $sr);
                    $this->display_section($course, $num, $sr, $level+1);
                }
                $this->display_insert_section_here($course, $section, null, $sr);
                echo html_writer::end_tag('ul'); // .eloflexsections
               
                //$this->elo_echo_end_section();
            }
            if ($addsectioncontrol = course_get_format($course)->get_add_section_control($sectionnum)) {
                echo $this->render($addsectioncontrol);
            }
        }
        echo html_writer::end_tag('div'); // .content
        echo html_writer::end_tag('li'); // .section
        $this->elo_echo_end_section();
        
        if ($level === 0) {
            if ($section->section) {
                $this->display_insert_section_here($course, $section->parent, null, $sr);
            }
            echo html_writer::end_tag('ul'); // .eloflexsections
            //$this->elo_echo_end_section();
        }
    }

    /**
     * Displays the target div for moving section (in 'moving' mode only)
     *
     * @param int|stdClass $courseorid current course
     * @param int|section_info $parent new parent section
     * @param null|int|section_info $before number of section before which we want to insert (or null if in the end)
     */
    protected function display_insert_section_here($courseorid, $parent, $before = null, $sr = null) {
        if ($control = course_get_format($courseorid)->get_edit_control_movehere($parent, $before, $sr)) {
            echo $this->render($control);
        }
    }

    /**
     * renders HTML for format_eloflexsections_edit_control
     *
     * @param format_eloflexsections_edit_control $control
     * @return string
     */
    protected function render_format_eloflexsections_edit_control(format_eloflexsections_edit_control $control) {
        if (!$control) {
            return '';
        }
        if ($control->class === 'movehere') {
            $icon = new pix_icon('movehere', $control->text, 'moodle', array('class' => 'movetarget', 'title' => $control->text));
            $action = new action_link($control->url, $icon, null, array('class' => $control->class));
            return html_writer::tag('li', $this->render($action), array('class' => 'movehere'));
        } else if ($control->class === 'cancelmovingsection' || $control->class === 'cancelmovingactivity') {
            return html_writer::tag('div', html_writer::link($control->url, $control->text),
                    array('class' => 'cancelmoving '.$control->class));
        } else if ($control->class === 'addsection') {
            $icon = new pix_icon('t/add', '', 'moodle', array('class' => 'iconsmall'));
            $text = $this->render($icon). html_writer::tag('span', $control->text, array('class' => $control->class.'-text'));
            $action = new action_link($control->url, $text, null, array('class' => $control->class));
            return html_writer::tag('div', $this->render($action), array('class' => 'mdl-right'));
        } else if ($control->class === 'backto') {
            $icon = new pix_icon('t/up', '', 'moodle');
            $text = $this->render($icon). html_writer::tag('span', $control->text, array('class' => $control->class.'-text'));
            return html_writer::tag('div', html_writer::link($control->url, $text),
                    array('class' => 'header '.$control->class));
        } else if ($control->class === 'settings' || $control->class === 'marker' || $control->class === 'marked') {
            $icon = new pix_icon('i/'. $control->class, $control->text, 'moodle', array('class' => 'iconsmall', 'title' => $control->text));
        } else if ($control->class === 'move' || $control->class === 'expanded' || $control->class === 'collapsed' ||
                $control->class === 'hide' || $control->class === 'show' || $control->class === 'delete') {
            $icon = new pix_icon('t/'. $control->class, $control->text, 'moodle', array('class' => 'iconsmall', 'title' => $control->text));
        } else if ($control->class === 'mergeup') {
            $icon = new pix_icon('mergeup', $control->text, 'format_eloflexsections', array('class' => 'iconsmall', 'title' => $control->text));
        }
        if (isset($icon)) {
            if ($control->url) {
                // icon with a link
                $action = new action_link($control->url, $icon, null, array('class' => $control->class));
                return $this->render($action);
            } else {
                // just icon
                return html_writer::tag('span', $this->render($icon), array('class' => $control->class));
            }
        }
        // unknown control
        return ' '. html_writer::link($control->url, $control->text, array('class' => $control->class)). '';
    }

    /**
     * If section is not visible, display the message about that ('Not available
     * until...', that sort of thing). Otherwise, returns blank.
     *
     * For users with the ability to view hidden sections, it shows the
     * information even though you can view the section and also may include
     * slightly fuller information (so that teachers can tell when sections
     * are going to be unavailable etc). This logic is the same as for
     * activities.
     *
     * @param stdClass $section The course_section entry from DB
     * @param bool $canviewhidden True if user can view hidden sections
     * @return string HTML to output
     */
    protected function section_availability_message($section, $canviewhidden) {
        global $CFG;
        $o = '';
        if (!$section->uservisible) {
            // Note: We only get to this function if availableinfo is non-empty,
            // so there is definitely something to print.
            $formattedinfo = \core_availability\info::format_info(
                $section->availableinfo, $section->course);
            $o .= html_writer::div($formattedinfo, 'availabilityinfo');
        } else if ($canviewhidden && !empty($CFG->enableavailability) && $section->visible) {
            $ci = new \core_availability\info_section($section);
            $fullinfo = $ci->get_full_information();
            if ($fullinfo) {
                $formattedinfo = \core_availability\info::format_info(
                    $fullinfo, $section->course);
                $o .= html_writer::div($formattedinfo, 'availabilityinfo');
            }
        }
        return $o;
    }

    /**
     * Displays a confirmation dialogue when deleting the section (for non-JS mode)
     *
     * @param stdClass $course
     * @param int $sectionreturn
     * @param int $deletesection
     */
    public function confirm_delete_section($course, $sectionreturn, $deletesection) {
        echo $this->box_start('noticebox');
        $courseurl = course_get_url($course, $sectionreturn);
        $optionsyes = array('confirm' => 1, 'deletesection' => $deletesection, 'sesskey' => sesskey());
        $formcontinue = new single_button(new moodle_url($courseurl, $optionsyes), get_string('yes'));
        $formcancel = new single_button($courseurl, get_string('no'), 'get');
        echo $this->confirm(get_string('confirmdelete', 'format_eloflexsections'), $formcontinue, $formcancel);
        echo $this->box_end();
    }
}
