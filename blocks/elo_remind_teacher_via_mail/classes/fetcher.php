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
 * File containing onlineusers class.
 *
 * @package    block_elo_reminder_users
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_elo_remind_teacher_via_mail;

defined('MOODLE_INTERNAL') || die();

/**
 * Class used to list and count elo reminder users
 *
 * @package    block_elo_reminder_users
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fetcher {

    /** @var string The SQL query for retrieving a list of elo reminder users */
    public $sql;
    /** @var string The SQL query for counting the number of elo reminder users */
    public $csql;
    /** @var string The params for the SQL queries */
    public $params;

    public $sitelevel;
    public $orderby;
    public $limitnum;
    public $paginatenum;

    /**
     * Class constructor
     *
     * @param int $currentgroup The group (if any) to filter on
     * @param int $now Time now
     * @param int $timetoshowteachers Number of seconds to show elo reminder teachers
     * @param int $timetoshowstudents Number of seconds to show elo reminder students
     * @param context $context Context object used to generate the sql for users enrolled in a specific course
     * @param bool $sitelevel Whether to check elo reminder users at site level.
     * @param int $courseid The course id to check
     */
    public function __construct($context) {
        $this->set_sql($context);
    }

    /**
     * Store the SQL queries & params for listing elo reminder users
     *
     * @param int $currentgroup The group (if any) to filter on
     * @param int $now Time now
     * @param int $timetoshowteachers Number of seconds to show elo reminder teachers
     * @param int $timetoshowstudents Number of seconds to show elo reminder students
     * @param context $context Context object used to generate the sql for users enrolled in a specific course
     * @param bool $sitelevel Whether to check elo reminder users at site level.
     * @param int $courseid The course id to check
     */
    protected function set_sql($context) {
        $this->sql = "SELECT DISTINCT u.id, u.email, u.firstname, u.lastname, r.shortname
        FROM {user} u
        INNER JOIN {role_assignments} ra ON ra.userid = u.id
        INNER JOIN {role} r ON r.id = ra.roleid
        WHERE r.shortname LIKE 'editingteacher' or r.shortname = 'teacher'
        ORDER BY u.id";
    }

    /**
     * Get a list of the most recent elo reminder users
     *
     * @param int $userlimit The maximum number of users that will be returned (optional, unlimited if not set)
     * @return array
     */
    public function get_users_export($userlimit = 0, $paginate = 0, $sort = '') {
        global $DB;
        if($sort == '' || !$sort){
          $this->sql .= " ".$this->orderby;
        }else{
            $this->sql .= " ORDER BY ".$sort;
        }

        if(!is_numeric($paginate)) $paginate = 0;
        $users = $DB->get_recordset_sql($this->sql, $this->params, $paginate, $userlimit);
        return $users;
    }

    /**
     * Get a list of the most recent elo reminder users
     *
     * @param int $userlimit The maximum number of users that will be returned (optional, unlimited if not set)
     * @return array
     */
    public function get_teachers($userlimit = 0, $paginate = 0, $sort = '') {
        global $DB;

        if($sort == '' || !$sort){
          $this->sql .= " ".$this->orderby;
        }else{
            $this->sql .= " ORDER BY ".$sort;
        }

        if(!is_numeric($paginate)) $paginate = 0;
        $users = $DB->get_records_sql($this->sql, $this->params, $paginate, $userlimit);
        return $users;
    }

    /**
     * Count the number of elo reminder users
     *
     * @return int
     */
    public function count_users() {
        global $DB;
        return $DB->count_records_sql($this->sql, $this->params);
    }

}
