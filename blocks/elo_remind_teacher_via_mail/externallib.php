<?php
/**
 * PLUGIN external file
 *
 * @package    block_plugin
 * @copyright  2019 Elo Tech
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('lib.php');
require_once("$CFG->libdir/externallib.php");


class block_elo_remind_teacher_via_mail_external extends external_api {
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function mailtouser_parameters() {
        // mailtouser_parameters() always return an external_function_parameters(). 
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
                // a external_description can be: external_value, external_single_structure or external_multiple structure
                array(
                    'teacherid' => new external_value(PARAM_INT, 'Teacher Id is invalid'),
                ) 
        );
    }
    
    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function mailtouser_returns() { // BIG concerns here. See below.
        return new external_single_structure(
            array(
                'elodata' => new external_value(PARAM_TEXT, 'Invalid JSON'),
            )
        );
    }
    
    /**
     * The function itself
     * @return string welcome message
     */
    public static function mailtouser($param_userid) {
        //Note: don't forget to validate the context and check capabilities
        $params = self::validate_parameters(self::mailtouser_parameters(),
                    array(
                        'teacherid' => $param_userid,
                    )
                  );
        $params['elodata'] = elo_sendmail_to_user($params);
        return $params;
    }
}

function response_to_js($result){
    return json_encode($result,JSON_FORCE_OBJECT);
}