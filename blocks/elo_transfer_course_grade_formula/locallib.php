<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class tool_customfirstaccesscourse_utils {

    /**
     * This class can not be instantiated
     */
    private function __construct() {
        
    }

    /**
     * Updates the translator database with the strings from files
     *
     * This should be executed each time before going to the translation page
     *
     * @param string $lang language code to checkout
     * @param progress_bar $progressbar optionally, the given progress bar can be updated
     */
    public static function liststudentinit() {
        global $DB;
        $sql = "SELECT DISTINCT(CONCAT_WS(u.id, '_', e.courseid)) AS id,u.id as userid, e.courseid as courseid
FROM
    {user} u
        JOIN
    {user_enrolments} ue ON ue.userid = u.id
        JOIN
    {enrol} e ON e.id = ue.enrolid
        JOIN
    {course} c ON c.id = e.courseid
        JOIN
    {role_assignments} ra ON ra.userid = u.id
        JOIN
    {context} ctx ON ra.contextid = ctx.id
        JOIN
    {role} r ON (r.id = ra.roleid)
WHERE
    1=1 AND u.deleted = 0
        AND r.shortname = 'student'
        AND ctx.contextlevel = 50
        AND FIND_IN_SET(ra.contextid,
            REPLACE(ctx.path, '/', ','))
ORDER BY u.id asc";
        $acts = $DB->get_records_sql($sql);
        return $acts;
    }

    public static function listteacherinit() {
        global $DB;
        $sql = "SELECT DISTINCT(CONCAT_WS(u.id, '_', e.courseid)) AS id,u.id as userid, e.courseid as courseid
FROM
    {user} u
        JOIN
    {user_enrolments} ue ON ue.userid = u.id
        JOIN
    {enrol} e ON e.id = ue.enrolid
        JOIN
    {course} c ON c.id = e.courseid
        JOIN
    {role_assignments} ra ON ra.userid = u.id
        JOIN
    {context} ctx ON ra.contextid = ctx.id
        JOIN
    {role} r ON (r.id = ra.roleid)
WHERE
    1=1 AND u.deleted = 0
        AND r.shortname = 'teacher'
        AND ctx.contextlevel = 50
        AND FIND_IN_SET(ra.contextid,
            REPLACE(ctx.path, '/', ','))
ORDER BY u.id asc";
        $acts = $DB->get_records_sql($sql);
        return $acts;
    }

    public static function countstudentinit() {
        global $DB;
        $count = $DB->count_records_sql("SELECT COUNT(DISTINCT(CONCAT_WS(u.id, '_', e.courseid))) as number
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id 
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ra.contextid = ctx.id
            JOIN {role} r ON (r.id = ra.roleid)
            WHERE 1 AND u.deleted = 0 AND r.shortname='student' AND ctx.contextlevel = 50 AND FIND_IN_SET(ra.contextid,REPLACE(ctx.path,'/',','))
            ORDER BY u.firstname, u.lastname");

        return $count;
    }

    public static function countteacherinit() {
        global $DB;
        $count = $DB->count_records_sql("SELECT COUNT(DISTINCT(CONCAT_WS(u.id, '_', e.courseid))) as number
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id 
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ra.contextid = ctx.id
            JOIN {role} r ON (r.id = ra.roleid)
            WHERE 1 AND u.deleted = 0 AND r.shortname='teacher' AND ctx.contextlevel = 50 AND FIND_IN_SET(ra.contextid,REPLACE(ctx.path,'/',','))
            ORDER BY u.firstname, u.lastname");

        return $count;
    }

    public static function checkout($lang, progress_bar $progressbar = null) {
        global $DB;
        $done = 0;
        $strinprogress = get_string('ou:checkoutinprogress', 'block_elo_reports_dssv');
        if ($lang == 'student') {
            $count = self::countstudentinit();
            foreach (self::liststudentinit() as $act) {
                if ($DB->record_exists('user_firstaccess', array('userid' => $act->userid, 'courseid' => $act->courseid))) {
                    $done++;
                    $donepercent = floor(min($done, $count) / $count * 100);
                    $progressbar->update_full($donepercent, $strinprogress);
                    continue;
                }
                $timeaccess = $DB->get_record_sql("SELECT COALESCE(min(timecreated), 0) AS firstaccess
                FROM {logstore_standard_log} WHERE action = 'viewed' and target='course' and userid = ? and courseid = ?
            ", array($act->userid, $act->courseid)); //get forum
                if ($timeaccess->firstaccess > 0) {
                    $lastaccess = new stdClass();
                    $lastaccess->userid = $act->userid;
                    $lastaccess->courseid = $act->courseid;
                    $lastaccess->firsttimeaccess = $timeaccess->firstaccess;
                    $DB->insert_record('user_firstaccess', $lastaccess);
                }
                if (!is_null($progressbar)) {
                    $done++;
                    $donepercent = floor(min($done, $count) / $count * 100);
                    $progressbar->update_full($donepercent, $strinprogress);
                }
            }
        } else {
            $count = self::countteacherinit();
            foreach (self::listteacherinit() as $act) {
                if ($DB->record_exists('user_firstaccess', array('userid' => $act->userid, 'courseid' => $act->courseid))) {
                    $done++;
                    $donepercent = floor(min($done, $count) / $count * 100);
                    $progressbar->update_full($donepercent, $strinprogress);
                    continue;
                }
                $timeaccess = $DB->get_record_sql("SELECT COALESCE(min(timecreated), 0) AS firstaccess
                FROM {logstore_standard_log} WHERE action = 'viewed' and target='course' and userid = ? and courseid = ?
            ", array($act->userid, $act->courseid)); //get forum
                if ($timeaccess->firstaccess > 0) {
                    $lastaccess = new stdClass();
                    $lastaccess->userid = $act->userid;
                    $lastaccess->courseid = $act->courseid;
                    $lastaccess->firsttimeaccess = $timeaccess->firstaccess;
                    $DB->insert_record('user_firstaccess', $lastaccess);
                }
                if (!is_null($progressbar)) {
                    $done++;
                    $donepercent = floor(min($done, $count) / $count * 100);
                    $progressbar->update_full($donepercent, $strinprogress);
                }
            }
        }

        if (!is_null($progressbar)) {
            $progressbar->update_full(100, get_string('checkoutdone', 'block_elo_reports_dssv'));
        }
    }
}

/**
 * Represents the action menu of the tool
 */
class tool_customfirstaccesscourse_menu implements renderable {

    /** @var menu items */
    protected $items = array();

    public function __construct(array $items = array()) {
        global $CFG;

        foreach ($items as $itemkey => $item) {
            $this->add_item($itemkey, $item['title'], $item['url'], empty($item['method']) ? 'post' : $item['method']);
        }
    }

    /**
     * Returns the menu items
     *
     * @return array (string)key => (object)[->(string)title ->(moodle_url)url ->(string)method]
     */
    public function get_items() {
        return $this->items;
    }

    /**
     * Adds item into the menu
     *
     * @param string $key item identifier
     * @param string $title localized action title
     * @param moodle_url $url action handler
     * @param string $method form method
     */
    public function add_item($key, $title, moodle_url $url, $method) {
        if (isset($this->items[$key])) {
            throw new coding_exception('Menu item already exists');
        }
        if (empty($title) or empty($key)) {
            throw new coding_exception('Empty title or item key not allowed');
        }
        $item = new stdclass();
        $item->title = $title;
        $item->url = $url;
        $item->method = $method;
        $this->items[$key] = $item;
    }

}
