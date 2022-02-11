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
require_once($CFG->dirroot . '/blocks/elo_remind_teacher_via_mail/lib.php');
use block_elo_remind_teacher_via_mail\fetcher_teacher;

$contextper = context_course::instance($COURSE->id);
$permissionaccesslink = has_capability('moodle/role:switchroles', $contextper);
$returnurl = $CFG->wwwroot . '/my/';
if (!$permissionaccesslink) {
    redirect($returnurl); // Khi khong co quyen thi khong the truy cap link truc tiep bang duong dan
}

$url = new moodle_url('/blocks/elo_remind_teacher_via_mail/index.php');
global $PAGE;
$PAGE->set_url($url);

$PAGE->set_context(context_system::instance());
$title = get_string('pluginname', 'block_elo_remind_teacher_via_mail');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);

// load css and js
page_requires($PAGE);

echo $OUTPUT->header();
echo $OUTPUT->container_start('block_elo_remind_teacher_via_mail');
require_login();

$teachers = new fetcher_teacher();
$renderable = new block_elo_remind_teacher_via_mail\output\main($teachers->get_teachers());
$renderer = $PAGE->get_renderer('block_elo_remind_teacher_via_mail');
echo $renderer->render_main($renderable);

echo $OUTPUT->container_end();
echo $OUTPUT->footer();