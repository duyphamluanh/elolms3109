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
 * Course format with flexible number of nested sections
 *
 * @package    format_eloflexsections
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// $plugin->version   = 2018082010;        // The current plugin version (Date: YYYYMMDDXX).
// $plugin->requires  = 2016120500;        // Requires this Moodle version
$plugin->version   = 2020110900;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2020110300;  
$plugin->release   = "3.2.2.ELO";
$plugin->maturity  = MATURITY_STABLE;
$plugin->component = 'format_eloflexsections';    // Full name of the plugin (used for diagnostics).
