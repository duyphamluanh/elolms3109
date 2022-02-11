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
require_once($CFG->dirroot.'/blocks/elo_unsubmit_assignment_reminder/lib.php');

/**
 * I dont know how to make a blocks.
 *
 * @package    block_elo_unsubmit_assignment_reminder
 * @copyright  Duy Pham <duy.pham@oude.edu.vn>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_elo_unsubmit_assignment_reminder extends block_base {

    /**
     * Init.
     */
    function init() {
        $this->title = get_string('pluginname', 'block_elo_unsubmit_assignment_reminder');
    }

    /**
     * This block does contain a configuration settings.
     *
     * @return boolean
     */
    function has_config() {return true;}

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

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // Main
        $renderable = new block_elo_unsubmit_assignment_reminder\output\main();
        $renderer = $this->page->get_renderer('block_elo_unsubmit_assignment_reminder');

        // $this->content->text = block_elo_unsubmit_assignment_reminder();
        $this->content->text = $renderer->render($renderable);
        if(!empty($this->content->text)){
            $this->content->text .= get_string("draft","block_elo_unsubmit_assignment_reminder");
        }
        return $this->content;
    }

    
    public function instance_allow_multiple() {
          return false;
    }
}
