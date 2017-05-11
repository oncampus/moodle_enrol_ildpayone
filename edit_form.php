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
 * Adds new instance of enrol_ildpayone to specified course
 * or edits current instance.
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class enrol_ildpayone_edit_form extends moodleform {

    function definition() {
        global $DB;
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $courseinfo = $DB->get_record('local_ildcourseinfo', array('course' => $instance->courseid));
        $data = json_decode($courseinfo->json);

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_ildpayone'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = array(ENROL_INSTANCE_ENABLED => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_ildpayone'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));

        $mform->addElement('text', 'customint1', get_string('product_id', 'enrol_ildpayone'));
        $mform->setType('customint1', PARAM_INT);
        $mform->setDefault('customint1', $data->attributes->offers[0]->productID);

        $mform->addElement('text', 'cost', get_string('cost', 'enrol_ildpayone'), array('size' => 4));
        $mform->setType('cost', PARAM_RAW); // Use unformat_float to get real value.
        $mform->setDefault('cost', format_float($data->attributes->offers[0]->price, 2, true));

        $payonecurrencies = $plugin->get_currencies();
        $mform->addElement('select', 'currency', get_string('currency', 'enrol_ildpayone'), $payonecurrencies);
        $mform->setDefault('currency', $data->attributes->offers[0]->priceCurrency);

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_ildpayone'), $roles);
        $mform->setDefault('roleid', '5');

        if (!empty($data->attributes->duration)) {
            $interval = new DateInterval($data->attributes->duration);
            $intervalInSeconds = new DateTime();
            $intervalInSeconds->setTimeStamp(0);
            $intervalInSeconds->add($interval);
            $period = $intervalInSeconds->getTimeStamp();
        } else {
            $period = 0;
        }

        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_ildpayone'), array('defaultunit' => 1));
        $mform->setDefault('enrolperiod', $period);
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_ildpayone');

        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_ildpayone'), array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_ildpayone');

        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_ildpayone'), array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_ildpayone');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context) = $this->_customdata;

        if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
            $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_ildpayone');
        }

        $cost = str_replace(get_string('decsep', 'langconfig'), '.', $data['cost']);
        if (!is_numeric($cost)) {
            $errors['cost'] = get_string('costerror', 'enrol_ildpayone');
        }

        return $errors;
    }
}
