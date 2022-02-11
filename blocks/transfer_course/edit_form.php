<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
//require_once($CFG->libdir.'/completionlib.php');

/**
 * The form for handling editing a course.
 */
class ou_transfer_course_edit_form extends moodleform {
//    protected $course;
//    protected $startdate;
//    public static $datefieldoptions = array('optional' => true);
    /**
     * Form definition.
     */
    function definition() {

        $mform    = $this->_form;
        $course        = $this->_customdata['course']; // this contains the data of this form
        $returnto = $this->_customdata['returnto'];
        $returnurl = $this->_customdata['returnurl'];


        // Form definition with new course defaults.
        $mform->addElement('header','general', get_string('general', 'form'));

        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);

        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);
   
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate'));//origin
        $mform->addHelpButton('startdate', 'startdate');
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('+1 day');
        $mform->setDefault('startdate', $date->getTimestamp());

        //nhien elo dich chuyen thoi gian 20_12_2019
        $choicetransfertimes = array();
        $choicetransfertimes['0'] = 'Không';
        $choicetransfertimes['1'] = 'Có';
        $mform->addElement('select', 'transfertimecourse', '<b class="text-danger">Bạn có muốn dịch chuyển thời gian môn học hay không ?</b>', $choicetransfertimes);
        $mform->addHelpButton('transfertimecourse', 'transfertimecourse');
        $mform->setDefault('transfertimecourse', $choicetransfertimes['0']);
        //End Nhien elo dich chuyen thoi gian 20_12_2019

        // When two elements we need a group.
        $buttonarray = array();
        $classarray = array('class' => 'form-submit');
        if ($returnto !== 0) {
            $buttonarray[] = &$mform->createElement('submit', 'saveandreturn', get_string('savechangesandreturn'), $classarray);
        }
        $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('savechangesanddisplay'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        // Finally set the current form data
        $this->set_data($course);
    }

    /**
     * Fill in the current page data for this course.
     */
    function definition_after_data() {
        global $DB;

        $mform = $this->_form;

        // add available groupings
        $courseid = $mform->getElementValue('id');
        if ($courseid and $mform->elementExists('defaultgroupingid')) {
            $options = array();
            if ($groupings = $DB->get_records('groupings', array('courseid'=>$courseid))) {
                foreach ($groupings as $grouping) {
                    $options[$grouping->id] = format_string($grouping->name);
                }
            }
            core_collator::asort($options);
            $gr_el =& $mform->getElement('defaultgroupingid');
            $gr_el->load($options);
        }

        // add course format options
        $formatvalue = $mform->getElementValue('format');
        if (is_array($formatvalue) && !empty($formatvalue)) {

            $params = array('format' => $formatvalue[0]);
            // Load the course as well if it is available, course formats may need it to work out
            // they preferred course end date.
            if ($courseid) {
                $params['id'] = $courseid;
            }
            $courseformat = course_get_format((object)$params);

            $elements = $courseformat->create_edit_form_elements($mform);
            for ($i = 0; $i < count($elements); $i++) {
                $mform->insertElementBefore($mform->removeElement($elements[$i]->getName(), false),
                        'addcourseformatoptionshere');
            }

            // Remove newsitems element if format does not support news.
            if (!$courseformat->supports_news()) {
                $mform->removeElement('newsitems');
            }
        }

        // Tweak the form with values provided by custom fields in use.
        // //use only 3.7
//        $handler  = core_course\customfield\course_handler::create();
//        $handler->instance_form_definition_after_data($mform, empty($courseid) ? 0 : $courseid);
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
//        global $DB;
    //Nhien
//        if($data['transfertimecourse']=="1"){
//            $data['enddate'] = $data['startdate'] + 60*60+24*7;
//        }
    //End Nhien
//        $errors = parent::validation($data, $files);
//
//        // Add field validation check for duplicate shortname.
//        if ($course = $DB->get_record('course', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
//            if (empty($data['id']) || $course->id != $data['id']) {
//                $errors['shortname'] = get_string('shortnametaken', '', $course->fullname);
//            }
//        }
//
//        // Add field validation check for duplicate idnumber.
//        if (!empty($data['idnumber']) && (empty($data['id']) || $this->course->idnumber != $data['idnumber'])) {
//            if ($course = $DB->get_record('course', array('idnumber' => $data['idnumber']), '*', IGNORE_MULTIPLE)) {
//                if (empty($data['id']) || $course->id != $data['id']) {
//                    $errors['idnumber'] = get_string('courseidnumbertaken', 'error', $course->fullname);
//                }
//            }
//        }
//        if ($errorcode = course_validate_dates($data)) {
//            $errors['enddate'] = get_string($errorcode, 'error');
//        }

//        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));
//
//        $courseformat = course_get_format((object)array('format' => $data['format']));
//        $formaterrors = $courseformat->edit_form_validation($data, $files, $errors);
//        if (!empty($formaterrors) && is_array($formaterrors)) {
//            $errors = array_merge($errors, $formaterrors);
//        }

        // Add the custom fields validation.
//        $handler = core_course\customfield\course_handler::create();
//        $errors  = array_merge($errors, $handler->instance_form_validation($data, $files));

        return true;
    }
    function course_validate_dates($coursedata) {
        // If both start and end dates are set end date should be later than the start date.
        if (!empty($coursedata['startdate']) && !empty($coursedata['enddate']) &&
                ($coursedata['enddate'] < $coursedata['startdate'])) {
            return 'enddatebeforestartdate';
        }

        // If start date is not set end date can not be set.
        if (empty($coursedata['startdate']) && !empty($coursedata['enddate'])) {
            return 'nostartdatenoenddate';
        }

        return false;
    }
}
