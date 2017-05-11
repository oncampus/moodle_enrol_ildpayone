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
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('payone/payone.php');
define('ILDPAYONE_INVOICE', 'ildpayone_invoice_data');

/**
 * Set parameters and get IFrame-Links.
 *
 * @param $instance
 * @param $course
 * @return mixed
 */
function ildpayone_prepare_purchase($instance, $course) {
    global $USER, $CFG;

    $payment_walls = array();
    $clearing_types = get_config('enrol_ildpayone', 'clearing_types');
    $clearing_types = explode(',', $clearing_types);

    foreach ($clearing_types as $type) {
        $payment_wall = new Payone();

        $invoice_data = ildpayone_get_invoice_data();
        $payment_wall->addCustomer($invoice_data);

        $product_id = $instance->customint1;
        $payment_wall->addItem($product_id, $instance->cost, 1, $course->fullname, 19);

        $hash_string = $course->id . $USER->id . $instance->id;
        $hash = hash_hmac("sha384", $hash_string, get_config('enrol_ildpayone', 'portalkey'));

        $reference = $instance->id . '-' . uniqid();

        $additional_parameter = array('reference' => $reference,
            'currency' => $instance->currency,
            'successurl' => $CFG->httpswwwroot . '/enrol/ildpayone/success.php?course=' . $course->id . '&user=' . $USER->id . '&instance=' . $instance->id . '&hash=' . $hash,
            'errorurl' => $CFG->httpswwwroot . '/course/view.php?id=' . $course->id,
            'backurl' => $CFG->httpswwwroot . '/course/view.php?id=' . $course->id,
            'targetwindow' => 'top'
        );

        $payment_wall->addParameter($additional_parameter);

        $payment_walls[$type] = $payment_wall->generateLink($type);
    }

    return $payment_walls;
}

/**
 * Get user invoice data.
 *
 * @return array|mixed
 */
function ildpayone_get_invoice_data() {
    global $DB, $USER;

    $invoice_data = $DB->get_record('user_preferences', array('userid' => $USER->id, 'name' => ILDPAYONE_INVOICE));

    if ($invoice_data) {
        return json_decode($invoice_data->value, true);
    } else {
        return array('customerid' => $USER->username);
    }
}

/**
 * Update user invoice data
 *
 * @param $invoice_data
 * @return bool
 */
function ildpayone_update_invoice_data($invoice_data) {
    global $DB;

    $user = $DB->get_record('user', array('username' => $invoice_data->customerid), 'id');

    if (!empty($user)) {
        $invoice_data_exists = $DB->get_record('user_preferences', array('userid' => $user->id, 'name' => ILDPAYONE_INVOICE));
        $invoice_json = json_encode($invoice_data);

        $record = new stdClass();
        $record->userid = $user->id;
        $record->name = ILDPAYONE_INVOICE;
        $record->value = $invoice_json;

        if ($invoice_data_exists) {
            $record->id = $invoice_data_exists->id;
            $return = $DB->update_record('user_preferences', $record);
        } else {
            $insertid = $DB->insert_record('user_preferences', $record);
            $return = (!empty($insertid)) ? true : false;
        }
    } else {
        $return = false;
    }

    return $return;
}