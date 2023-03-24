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
 * Inquiry form of enrol_ildpayone.
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class ildpayone_inquiry_form extends moodleform {

    function definition() {
        global $USER;

        $mform = $this->_form;

        $mform->addElement('hidden', 'cartid', $this->_customdata['cartid']);
        $mform->setType('cartid', PARAM_TEXT);

        $mform->addElement('html', '<h3>Ihre persönlichen Daten</h3>');

        $salutation = array('Herr' => 'Herr', 'Frau' => 'Frau', 'keine Angabe' => 'keine Angabe');
        $mform->addElement('select', 'salutation', get_string('salutation', 'enrol_ildpayone'), $salutation);
        $mform->addRule('salutation', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'company', get_string('company', 'enrol_ildpayone'), 'maxlength="100" size="30"');
        $mform->setType('company', PARAM_TEXT);

        $namefields = array('firstname', 'lastname', 'address', 'city');

        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
            if (!empty($USER->$field)) {
                $mform->setDefault($field, $USER->$field);
            }
        }

        $mform->addElement('text', 'zip', get_string('zip', 'enrol_ildpayone'), 'maxlength="6" size="6"');
        $mform->setType('zip', PARAM_INT);
        $mform->addRule('zip', get_string('required'), 'required', null, 'client');
        if (!empty($USER->profile_field_zipcode)) {
            $mform->setDefault('zip', $USER->profile_field_zipcode);
        }

        $country             = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country             = array_merge($default_country, $country);
        $mform->addElement('select', 'country', get_string('country'), $country);
        $mform->addRule('country', get_string('required'), 'required', null, 'client');
        if (!empty($USER->country)) {
            $mform->setDefault('country', $USER->country);
        }

        $mform->addElement('text', 'birthday', get_string('birthday', 'enrol_ildpayone'), 'maxlength="10" size="30"');
        $mform->setType('birthday', PARAM_TEXT);
        $mform->addRule('birthday', get_string('required'), 'required', null, 'client');
        if (!empty($USER->profile_field_birthdate)) {
            $mform->setDefault('birthday', $USER->profile_field_birthdate);
        }

        $mform->addElement('text', 'phone1', get_string('phone1'), 'maxlength="100" size="30"');
        $mform->setType('phone1', core_user::get_property_type('phone1'));
        $stringid = 'missing' . 'phone1';
        if (!get_string_manager()->string_exists($stringid, 'moodle')) {
            $stringid = 'required';
        }
        $mform->addRule('phone1', get_string($stringid), 'required', null, 'client');
        if (!empty($USER->phone1)) {
            $mform->setDefault('phone1', $USER->phone1);
        }

        $mform->addElement('html', '<h3>Rechnungsinformationen</h3>');

        $mform->addElement('checkbox', 'invoice', get_string('invoice', 'enrol_ildpayone'));

        $namefields = array('firstname', 'lastname', 'address', 'city');

        $mform->addElement('text', 'invoice_company', get_string('company', 'enrol_ildpayone'), 'maxlength="100" size="30"');
        $mform->setType('invoice_company', PARAM_TEXT);
        $mform->disabledIf('invoice_company', 'invoice');

        $mform->addElement('text', 'invoice_email', get_string('invoice_email', 'enrol_ildpayone'));
        $mform->setType('invoice_email', PARAM_TEXT);
        $mform->addRule('invoice_email', get_string('invoice_email_prompt', 'enrol_ildpayone'), 'email');
        $mform->disabledIf('invoice_email', 'invoice');

        foreach ($namefields as $field) {
            $mform->addElement('text', 'invoice_' . $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType('invoice_' . $field, core_user::get_property_type('firstname'));
            $mform->disabledIf('invoice_' . $field, 'invoice');
        }

        $mform->addElement('text', 'invoice_zip', get_string('zip', 'enrol_ildpayone'), 'maxlength="6" size="6"');
        $mform->setType('invoice_zip', PARAM_INT);
        $mform->disabledIf('invoice_zip', 'invoice');

        $mform->addElement('text', 'invoice_order_number', get_string('order_number', 'enrol_ildpayone'), 'maxlength="6" size="6"');
        $mform->setType('invoice_order_number', PARAM_TEXT);
        $mform->disabledIf('invoice_order_number', 'invoice');

        $country             = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country             = array_merge($default_country, $country);
        $mform->addElement('select', 'invoice_country', get_string('country'), $country);
        $mform->disabledIf('invoice_country', 'invoice');

        $this->add_action_buttons(true, get_string('inquiry', 'enrol_ildpayone'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /*
        if (!preg_match('(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d
', $data['birthday'])) {
            $errors['birthday'] = 'Folgendes format verwenden';

            return $errors;
        }*/

        return $errors;
    }
}
