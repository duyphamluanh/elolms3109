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
 * @package    block_elo_remind_teacher_via_mail
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading(
    'sampleheader',
    get_string('headerconfig', 'block_elo_remind_teacher_via_mail'),
    get_string('descconfig', 'block_elo_remind_teacher_via_mail')
));

$settings->add(new admin_setting_configtext(
    'block_elo_remind_teacher_via_mail/gaptime',
    get_string('labelgaptime', 'block_elo_remind_teacher_via_mail'),
    get_string('descgaptime', 'block_elo_remind_teacher_via_mail'),
    2, PARAM_INT
));

$settings->add(new admin_setting_configtext(
    'block_elo_remind_teacher_via_mail/sendmaildelay',
    get_string('labelsendmaildelay', 'block_elo_remind_teacher_via_mail'),
    get_string('descsendmaildelay', 'block_elo_remind_teacher_via_mail'),
    30, PARAM_INT
));
