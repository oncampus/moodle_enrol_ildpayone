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
 * Payone enrolment plugin.
 *
 * This plugin allows you to set up paid courses.
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');
global $DB, $USER;

$portalkey_hash = hash("md5", get_config('enrol_ildpayone', 'portalkey'));

if ($_REQUEST['key'] == $portalkey_hash) {
    #TODO: check request from ip range
    $request = $_REQUEST;
    $portalkey = get_config('enrol_ildpayone', 'portalkey');

    // get user from username
    $user = $DB->get_record('user', array('username' => utf8_encode($request['customerid'])), 'username, firstname, lastname, email, lang, city, country');
    $user = (array)$user;

    // create user hash
    ksort($user);

    $hash_string = '';
    foreach ($user as $key => $value) {
        $hash_string .= $value;
    }

    $hash = hash_hmac("sha384", $hash_string, $portalkey);
    $user['hash'] = $hash;

    // create payone data hash
    ksort($request);

    $hash_string_data = '';
    foreach ($request as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $i => $i_value) {
                $i_value = utf8_encode($i_value);
                $hash_string_data .= $i_value;
                $request[$key][$i] = $i_value;
            }
        } else {
            $value = utf8_encode($value);
            $hash_string_data .= $value;
            $request[$key] = $value;
        }
    }

    #ildpayone_write_log($hash_string);

    $hash = hash_hmac("sha384", $hash_string_data, $portalkey);

    $payone_data = array('payone_data' => json_encode($request), 'hash' => $hash, 'user' => json_encode($user));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ''); // Set URL.
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payone_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $invoice = json_decode($result);

    if ($invoice->success == true) {
        $invoice_data = new stdClass();
        $invoice_data->customerid = $_REQUEST['customerid'];
        $invoice_data->company = $_REQUEST['company'];
        $invoice_data->firstname = $_REQUEST['firstname'];
        $invoice_data->lastname = $_REQUEST['lastname'];
        $invoice_data->street = $_REQUEST['street'];
        $invoice_data->zip = $_REQUEST['zip'];
        $invoice_data->city = $_REQUEST['city'];
        $invoice_data->email = $_REQUEST['email'];
        $invoice_data->country = $_REQUEST['country'];

        $invoice_update = ildpayone_update_invoice_data($invoice_data);

        if ($invoice_update) {
            $txlog = new stdClass();
            $txlog->txid = $_REQUEST['txid'];
            $txlog->username = $_REQUEST['customerid'];
            $txlog->txaction = $_REQUEST['txaction'];
            $txlog->log = $request;
            $txlog->timeupdated = time();

            $record_exist = $DB->record_exists('enrol_ildpayone', array('txid' => $txlog->txid, 'txaction' => $txlog->txaction));

            if (!$record_exist) {
                $insertid = $DB->insert_record('enrol_ildpayone', $txlog);

                $subject = 'Neue Anmeldung - ' . $request['de']['1'] . ' - ' . $user['firstname'] . ' ' . $user['lastname'] . ' - Status: ' . $request['txaction'];
                $messagetext = json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $messagehtml = '<pre>' . htmlentities(json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre>';

                $dummy = core_user::get_noreply_user();
                $dummy->firstname = 'Payone';
                $dummy->lastname = ' at oncampus';
                $dummy->email = 'payment@oncampus.de';

                $mail_sent = email_to_user($dummy, $USER, $subject, $messagetext, $messagehtml);

                if ($mail_sent) {
                    print 'TSOK';
                } else {
                    print 'mail not sent';
                }
            } else {
                print 'TSOK';
            }
        } else {
            print 'invoice_update failed';
        }
    } else {
        print $invoice->error;
    }
}

function ildpayone_write_log($data) {
    $logfile = 'status.log';
    #$data = date("Y-m-d H:i:s") . PHP_EOL . print_r($_REQUEST, true) . PHP_EOL . PHP_EOL;

    file_put_contents($logfile, $data . PHP_EOL . PHP_EOL, FILE_APPEND);
}