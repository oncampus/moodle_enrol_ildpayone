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
require_once($CFG->dirroot . '/enrol/ildpayone/classes/cart.php');
define('ILDPAYONE_INVOICE', 'ildpayone_invoice_data');
define('ILDPAYONE_NUMBER_PRECISION', 2);

/**
 * Set parameters and get IFrame-Links.
 *
 * @param $instance
 * @param $course
 * @return mixed
 */
function ildpayone_prepare_purchase() {
    global $USER, $CFG, $SESSION;

    $payment_walls = array();
    $clearing_types = get_config('enrol_ildpayone', 'clearing_types');
    $clearing_types = explode(',', $clearing_types);

    foreach ($clearing_types as $type) {
        $payment_wall = new Payone();

        $invoice_data = ildpayone_get_invoice_data();
        $payment_wall->addCustomer($invoice_data);

        $cart = new \ildpayone\cart\Cart();
        $vat = $cart->get_vat();

        foreach ($cart->get_products() as $id => $product) {
            // item type - goods, shipment, handling, voucher
            #$productID = $product->get_data()->attributes->offers[0]->productID;
            $price = $product->get_original_price();
            $qty = $product->get_quantity();
            $title = $product->get_title();
            $instance = $product->get_enrol_instance();
            $pvat = $product->get_tax_rate();

            $payment_wall->addItem('goods', $id, $price, $qty, $title, $pvat);

            if ($product->has_graduation_discount()) {
                $graduation_discount = $product->get_graduation_discount();
                $title = 'Staffelrabatt';
                $graduation_discount_rate = -($product->get_original_price() * ($graduation_discount->discountValue / 100));

                $payment_wall->addItem('voucher', $product->get_graduation_discount()->id, $graduation_discount_rate, $qty, $title, $pvat);
            }

            if ($product->has_discount()) {
                $discount = $product->get_discount();
                $title = get_string('code', 'enrol_ildpayone') . get_string('redeemed-coupon', 'enrol_ildpayone', array('code' => $discount->code, 'discount' => $discount->discountValue . '%'));

                if ($product->has_graduation_discount()) {
                    $graduation_discount = $product->get_graduation_discount();
                    $graduation_discount_price = $product->get_original_price() - ($product->get_original_price() * ($graduation_discount->discountValue / 100));
                    $discount_rate = -($graduation_discount_price * ($discount->discountValue / 100));
                } else {
                    $discount_rate = -($product->get_original_price() * ($discount->discountValue / 100));
                }

                $payment_wall->addItem('voucher', $discount->id, $discount_rate, $qty, $title, $pvat);
            }
        }

        if ($cart->has_discount()) {
            $cart_discount = $cart->get_discount();
            $cart_discount_rate = -($cart_discount->discountValue);
            $title = get_string('code', 'enrol_ildpayone') . get_string('redeemed-coupon', 'enrol_ildpayone', array('code' => $cart_discount->code, 'discount' => $cart_discount->discountValue . 'EUR'));

            $payment_wall->addItem('voucher', $cart_discount->id, $cart_discount_rate, 1, $title, $vat);
        }

        $hash_string = $USER->id . $cart->get_id();
        $hash = hash_hmac("sha384", $hash_string, get_config('enrol_ildpayone', 'portalkey'));

        $reference = $USER->id . '-' . uniqid();

        $additional_parameter = array('reference' => $reference,
            'currency' => 'EUR',
            'successurl' => $CFG->httpswwwroot . '/enrol/ildpayone/success.php?user=' . $USER->id . '&hash=' . $hash,
            'errorurl' => $CFG->httpswwwroot . '/course/view.php?id=' . $cart->get_enrolid(),
            'backurl' => $CFG->httpswwwroot . '/course/view.php?id=' . $cart->get_enrolid(),
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
        $data = json_decode($invoice_data->value, true);

        if ($data['customerid'] != $USER->id) {
            $data['customerid'] = $USER->id;
        }

        #return json_decode($invoice_data->value, true);
        return $data;
    } else {
        return array('customerid' => $USER->id);
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

    #$user = $DB->get_record('user', array('username' => $invoice_data->customerid), 'id');
    $userid = $invoice_data->customerid;

    if (!empty($userid)) {
        $invoice_data_exists = $DB->get_record('user_preferences', array('userid' => $userid, 'name' => ILDPAYONE_INVOICE));
        $invoice_json = json_encode($invoice_data);

        $record = new stdClass();
        $record->userid = $userid;
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

/**
 * Get Inquiry form data & create moodalis invoice.
 *
 * @param $formdata
 * @return bool
 */
function ildpayone_create_inquiry($formdata) {
    global $USER, $DB, $CFG;

    require_once($CFG->dirroot . '/user/profile/lib.php');

    $portalkey = get_config('enrol_ildpayone', 'portalkey');

    $cart = new \ildpayone\cart\Cart();

    $data = (array)$formdata;
    $data['userid'] = $USER->id;
    $data['username'] = $USER->username;
    $data['email'] = $USER->email;

    $zfu_products = $cart->get_zfu_products();

    foreach ($zfu_products as $id => $product) {
        $data['type'][] = 'goods';
        $data['id'][] = $id;
        $data['no'][] = $product->get_quantity();
        $data['pr'][] = $product->get_original_price();
        $data['de'][] = $product->get_title();
        $data['va'][] = $product->get_tax_rate();
        $data['ref'][] = null;

        if ($product->has_discount()) {
            $discount = $product->get_discount();
            $discount_rate = -($product->get_original_price() * ($discount->discountValue / 100));
            $title = get_string('code', 'enrol_ildpayone') . get_string('redeemed-coupon', 'enrol_ildpayone', array('code' => $discount->code, 'discount' => $discount->discountValue . '%'));

            $data['type'][] = 'voucher';
            $data['id'][] = $discount->id;
            $data['no'][] = $product->get_quantity();
            $data['pr'][] = $discount_rate;
            $data['de'][] = $title;
            $data['va'][] = 0;
            $data['ref'][] = $id;
        }
    }

    if ($cart->has_zfu_discount()) {
        $zfu_discount = $cart->get_zfu_discount();
        $zfu_discount_rate = -($zfu_discount->discountValue);
        $title = get_string('code', 'enrol_ildpayone') . get_string('redeemed-coupon', 'enrol_ildpayone', array('code' => $zfu_discount->code, 'discount' => $zfu_discount->discountValue . 'EUR'));

        $data['type'][] = 'voucher';
        $data['id'][] = $zfu_discount->id;
        $data['no'][] = 1;
        $data['pr'][] = $zfu_discount_rate;
        $data['de'][] = $title;
        $data['va'][] = 0;
        $data['ref'][] = null;
    }

    ksort($data);

    $hash_string_data = '';
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $i => $i_value) {
                $hash_string_data .= $i_value;
                $data[$key][$i] = $i_value;
            }
        } else {
            $hash_string_data .= $value;
            $data[$key] = $value;
        }
    }

    $hash = hash_hmac("sha384", $hash_string_data, $portalkey);

    $data = array('data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'hash' => $hash);

    $request = new stdClass();
    $request->type = 'inquiry';
    $request->url = 'https://moodalis.oncampus.de/fhl/inquiry.php';
    $request->data = json_encode($data, JSON_UNESCAPED_UNICODE);

    $insertid = $DB->insert_record('enrol_ildpayone_requests', $request);

    if (!empty($insertid)) {
        if (isset($USER->profile_field_zipcode) && empty($USER->profile_field_zipcode) || isset($USER->profile_field_birthdate) && empty($USER->profile_field_birthdate)) {
            $USER->profile_field_zipcode = $formdata->zip;
            $USER->profile_field_birthdate = $formdata->birthday;

            profile_save_data($USER);
        }

        return true;
    } else {
        return false;
    }
}

/**
 * Create invoice at moodalis for free access.
 *
 * @return bool
 */
function ildpayone_create_free_access_invoice() {
    global $USER, $CFG, $DB;
    $invoice_data = array();

    $cart = new \ildpayone\cart\Cart();
    $vat = $cart->get_vat();

    $reference = $USER->id . '-' . uniqid();
    $invoice_data['reference'] = $reference;
    $invoice_data['key'] = hash("md5", get_config('enrol_ildpayone', 'portalkey'));
    $invoice_data['customerid'] = $USER->id;
    $invoice_data['txid'] = uniqid($USER->id);
    $invoice_data['txaction'] = 'free';
    $invoice_data['clearingtype'] = 'free';

    $invoice_data['company'] = '';
    $invoice_data['firstname'] = $USER->firstname;
    $invoice_data['lastname'] = $USER->lastname;
    $invoice_data['street'] = $USER->address;
    $invoice_data['zip'] = '';
    $invoice_data['city'] = $USER->city;
    $invoice_data['country'] = $USER->country;
    $invoice_data['email'] = $USER->email;

    $i = 1;
    foreach ($cart->get_products() as $id => $product) {
        // item type - goods, shipment, handling, voucher
        $price = $product->get_original_price();
        $qty = $product->get_quantity();
        $title = $product->get_title();

        $invoice_data['type'][$i] = 'goods';
        $invoice_data['id'][$i] = $id;
        $invoice_data['pr'][$i] = $price;
        $invoice_data['no'][$i] = $qty;
        $invoice_data['de'][$i] = $title;
        $invoice_data['va'][$i] = $vat;
        $invoice_data['ref'][$i] = '';

        if ($product->has_discount()) {
            $i++;
            $discount = $product->get_discount();
            $title = get_string('code', 'enrol_ildpayone') . get_string('redeemed-coupon', 'enrol_ildpayone', array('code' => $discount->code, 'discount' => $discount->discountValue . '%'));

            if ($product->has_graduation_discount()) {
                $graduation_discount = $product->get_graduation_discount();
                $graduation_discount_price = $product->get_original_price() - ($product->get_original_price() * ($graduation_discount->discountValue / 100));
                $discount_rate = -($graduation_discount_price * ($discount->discountValue / 100));
            } else {
                $discount_rate = -($product->get_original_price() * ($discount->discountValue / 100));
            }

            $invoice_data['type'][$i] = 'voucher';
            $invoice_data['id'][$i] = $discount->id;
            $invoice_data['pr'][$i] = $discount_rate;
            $invoice_data['no'][$i] = $qty;
            $invoice_data['de'][$i] = $title;
            $invoice_data['va'][$i] = $vat;
            $invoice_data['ref'][$i] = $id;
        }

        $i++;
    }

    $request = new stdClass();
    $request->type = 'free_access';
    $request->url = $CFG->wwwroot . '/enrol/ildpayone/status.php';
    $request->data = urldecode(http_build_query($invoice_data, '', '&'));
    $request->is_active = 0;
    $request->successful = 0;

    $insertid = $DB->insert_record('enrol_ildpayone_requests', $request);

    if (!empty($insertid)) {
        return true;
    } else {
        return false;
    }
}

function ildpayone_send_requests() {
    global $DB, $USER;
    $send_mail = false;

    $is_active = $DB->get_record('enrol_ildpayone_requests', array('is_active' => 1));

    if (!$is_active) {
        set_time_limit(0);

        $requests = $DB->get_records('enrol_ildpayone_requests', array('successful' => 0), '', '*', 0, 10);

        if (!empty($requests)) {
            foreach ($requests as $request) {
                
                #mtrace(print_r($request,true));
                
                $dataobject = new stdClass();
                $dataobject->id = $request->id;
                $dataobject->is_active = 1;
                $dataobject->timestarted = time();

                $updated = $DB->update_record('enrol_ildpayone_requests', $dataobject);

                if ($updated) {
                    $json = json_decode($request->data);
                    if (is_object($json) && json_last_error() == JSON_ERROR_NONE) {
                        $data = $json;
                    } else {
                        $data = mb_convert_encoding($request->data, 'ISO-8859-1');
                    }

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $request->url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $result = curl_exec($ch);
                    $err = curl_errno($ch);
                    $errmsg = curl_error($ch);
                    $header = curl_getinfo($ch);

                    if ($request->type == 'inquiry') {
                        $inquiry_data = json_decode($result);
                        $send_mail = true;
                    }
                    
                    if ($result == 'TSOK' || (isset($inquiry_data) && $inquiry_data->success)) {
                        $dataobject->is_active = 0;
                        $dataobject->successful = 1;
                        $dataobject->timefinished = time();
                    } else {
                        $dataobject->is_active = 0;
                    }

                    $DB->update_record('enrol_ildpayone_requests', $dataobject);

                    if ($send_mail) {
                        $entry = json_decode($request->data);
                        $inquiry = json_decode($entry->data, true);

                        $subject = 'Neue Anfrage - ' . $inquiry['de'][0] . ' - ' . $inquiry['firstname'] . ' ' . $inquiry['lastname'];
                        $messagetext = json_encode($inquiry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        $messagehtml = '<pre>' . htmlentities(json_encode($inquiry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre>';

                        $dummy = core_user::get_noreply_user();
                        $dummy->firstname = 'Payone';
                        $dummy->lastname = ' at oncampus';
                        $dummy->email = 'payment@oncampus.de';

                        $mail_sent = email_to_user($dummy, $USER, $subject, $messagetext, $messagehtml);
                    }
                }
            }
        }
    }
}