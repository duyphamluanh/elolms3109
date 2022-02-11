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

defined('MOODLE_INTERNAL') || die();

/**
 * I dont know how to make a blocks.
 *
 * @package    block_elo_transfer_course_grade_formula
 * @copyright  Duy Pham <duy.pham@oude.edu.vn>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_elo_transfer_course_grade_formula extends block_base {

    /**
     * Init.
     */
    function init() {
        $this->title = get_string('pluginname', 'block_elo_transfer_course_grade_formula');
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    function get_content() {
        global $CFG, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return $this->content;
        }
        $context = context_block::instance($this->instance->id);
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        $icon = $OUTPUT->pix_icon('i/course', get_string('course'));
        if(!has_capability('block/elo_transfer_course_grade_formula:viewindashboard', $context)){
            return $this->content;
        }
        $this->content->footer = $icon."<a href=\"$CFG->wwwroot/blocks/elo_transfer_course_grade_formula/index.php\">".get_string("directransferlink","block_elo_transfer_course_grade_formula")."</a> ...";  
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

    public function instance_allow_multiple() {
          return false;
    }

    /**
     * This block does contain a configuration settings.
     *
     * @return boolean
     */
    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
             
                 // do something
                  
                      return true;
    }
}
