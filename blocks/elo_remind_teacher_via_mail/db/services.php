<?php

$services = array(
    'block_elo_remind_teacher_via_mail' => array(                      //the name of the web service
        'functions' => array ('block_elo_remind_teacher_via_mail_mailtouser'), //web service functions of this service 
                                                                    //web service function name
        'requiredcapability' => '',                //if set, the web service user need this capability to access 
                                                    //any function of this service. For example: 'some/capability:specified'                 
        'restrictedusers' =>0,                      //if enabled, the Moodle administrator must link some user to this service
                                                    //into the administration
        'enabled'=>1,                               //if enabled, the service can be reachable on a default installation
                                                    //used only when installing the services
        'shortname'=>'eloreminderteacherservice' //the short name used to refer to this service from elsewhere including when fetching a token
    )
  );

$functions = array(
    'block_elo_remind_teacher_via_mail_mailtouser' => array(
        'classname' => 'block_elo_remind_teacher_via_mail_external',
        'methodname' => 'mailtouser',
        'classpath' => 'blocks/elo_remind_teacher_via_mail/externallib.php',
        'description' => 'Sending a reminder to teacher',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ),
);


