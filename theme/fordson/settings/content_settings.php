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
* Heading and course images settings page file.
*
* @packagetheme_fordson
* @copyright  2016 Chris Kenniburg
* @creditstheme_fordson - MoodleHQ
* @licensehttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_fordson_content', get_string('contentsettings', 'theme_fordson'));
// Content Info
$name = 'theme_fordson/textcontentinfo';
$heading = get_string('textcontentinfo', 'theme_fordson');
$information = get_string('textcontentinfodesc', 'theme_fordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Frontpage Textbox.
$name = 'theme_fordson/fptextbox';
$title = get_string('fptextbox', 'theme_fordson');
$description = get_string('fptextbox_desc', 'theme_fordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Frontpage Textbox Logged Out.
$name = 'theme_fordson/fptextboxlogout';
$title = get_string('fptextboxlogout', 'theme_fordson');
$description = get_string('fptextboxlogout_desc', 'theme_fordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Alert setting.
$name = 'theme_fordson/alertbox';
$title = get_string('alert', 'theme_fordson');
$description = get_string('alert_desc', 'theme_fordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);


// // Elo: Duy Button extra one name
$name = 'theme_fordson/contentbuttonextraone';
$title = get_string('contentbuttonextraone', 'theme_fordson');
$description = get_string('contentbuttonextraone_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW, 50);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// // Elo: Duy Button extra one name
$name = 'theme_fordson/contentbuttonextraoneurl';
$title = get_string('contentbuttonextraoneurl', 'theme_fordson');
$description = get_string('contentbuttonextraoneurl_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_RAW, $cols='10', $rows='1');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);


// // Elo: Duy Button extra two name
$name = 'theme_fordson/contentbuttonextratwo';
$title = get_string('contentbuttonextratwo', 'theme_fordson');
$description = get_string('contentbuttonextratwo_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW, 50);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// // Elo: Duy Button extra two name
$name = 'theme_fordson/contentbuttonextratwourl';
$title = get_string('contentbuttonextratwourl', 'theme_fordson');
$description = get_string('contentbuttonextratwourl_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_RAW, 10, 1);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);



// // Elo: Duy Button extra three name
$name = 'theme_fordson/contentbuttonextrathree';
$title = get_string('contentbuttonextrathree', 'theme_fordson');
$description = get_string('contentbuttonextrathree_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW, 50);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// // Elo: Duy Button extra three name
$name = 'theme_fordson/contentbuttonextrathreeurl';
$title = get_string('contentbuttonextrathreeurl', 'theme_fordson');
$description = get_string('contentbuttonextrathreeurl_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_RAW, 10, 1);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// // Elo: Duy Button extra three name
$name = 'theme_fordson/technicalsupportmobile';
$title = get_string('technicalsupportmobile', 'theme_fordson');
$description = get_string('technicalsupportmobile_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW, 20);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// // Elo: Duy Button extra three name
$name = 'theme_fordson/technicalsupportemail';
$title = get_string('technicalsupportemail', 'theme_fordson');
$description = get_string('technicalsupportemail_desc', 'theme_fordson');
$default = "";
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW, 30);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);


// Must add the page after definiting all the settings!
$settings->add($page);
