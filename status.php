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

define('PAYONE_TX_IP', '185.60.20.0/24');

ob_start();

require_once('../../config.php');
require_once('locallib.php');
global $DB, $USER, $PAGE;

$PAGE->set_context(context_system::instance());

$portalkey_hash = hash("md5", get_config('enrol_ildpayone', 'portalkey'));

ildpayone_write_log($portalkey_hash);

ildpayone_write_log(print_r($_REQUEST,true));


if ($_REQUEST['key'] == $portalkey_hash) {
    #TODO: check request from ip range
    $request   = $_REQUEST;
    $portalkey = get_config('enrol_ildpayone', 'portalkey');

    // get user from username
    $user = $DB->get_record('user', array('id' => utf8_encode($request['customerid'])), 'id, username, firstname, lastname, email, lang, city, country');

    $user = (array)$user;

    // create user hash
    ksort($user);

    $hash_string = '';
    foreach ($user as $key => $value) {
        $hash_string .= $value;
    }

    $hash         = hash_hmac("sha384", $hash_string, $portalkey);
    $user['hash'] = $hash;

    // create payone data hash
    ksort($request);

    $hash_string_data = '';

    ildpayone_write_log(print_r($request, true));

    foreach ($request as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $i => $i_value) {
                $i_value           = utf8_encode($i_value);
                $hash_string_data  .= $i_value;
                $request[$key][$i] = $i_value;
            }
        } else {
            $value            = utf8_encode($value);
            $hash_string_data .= $value;
            $request[$key]    = $value;
        }
    }

    $hash = hash_hmac("sha384", $hash_string_data, $portalkey);

    $payone_data = array('payone_data' => json_encode($request), 'hash' => $hash, 'user' => json_encode($user));

    ildpayone_write_log(print_r($payone_data, true));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://moodalis.oncampus.de/fhl/payone.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payone_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result  = curl_exec($ch);
    $invoice = json_decode($result);

    ildpayone_write_log($result);

    if ($invoice->success == true) {
        $invoice_data             = new stdClass();
        $invoice_data->customerid = $request['customerid'];
        $invoice_data->company    = $request['company'];
        $invoice_data->firstname  = $request['firstname'];
        $invoice_data->lastname   = $request['lastname'];
        $invoice_data->street     = $request['street'];
        $invoice_data->zip        = $request['zip'];
        $invoice_data->city       = $request['city'];
        $invoice_data->email      = $request['email'];
        $invoice_data->country    = $request['country'];

        $invoice_update = ildpayone_update_invoice_data($invoice_data);

        if ($invoice_update) {
            $txlog              = new stdClass();
            $txlog->txid        = $request['txid'];
            $txlog->userid      = $user['id'];
            $txlog->txaction    = $request['txaction'];
            $txlog->log         = json_encode($request);
            $txlog->timeupdated = time();

            $record_exist = $DB->record_exists('enrol_ildpayone', array('txid' => $txlog->txid, 'txaction' => $txlog->txaction));

            if (!$record_exist) {
                $insertid = $DB->insert_record('enrol_ildpayone', $txlog);

                if (count($request['de']) > 1) {
                    $coursename = 'Mehrere Kurse';
                } else {
                    $coursename = $request['de']['1'];
                }

                $subject     = 'Neue Anmeldung - ' . $coursename . ' - ' . $user['firstname'] . ' ' . $user['lastname'] . ' - Status: ' . $request['txaction'];
                $messagetext = json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $messagehtml = '<pre>' . htmlentities(json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre>';

                $dummy            = core_user::get_noreply_user();
                $dummy->firstname = 'Payone';
                $dummy->lastname  = ' at oncampus';
                $dummy->email     = 'payment@oncampus.de';

                $mail_sent = email_to_user($dummy, $USER, $subject, $messagetext, $messagehtml);

                if ($mail_sent) {
		ob_end_clean();
                    print 'TSOK';
                } else {
                    print 'mail not sent';
                }
            } else {
	      ob_end_clean();
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

    file_put_contents($logfile, date("Y-m-d H:i:s") . PHP_EOL . $data . PHP_EOL . PHP_EOL, FILE_APPEND);
}

/*
$response = array(
    "key" => "49a10a44ac0d61a7debead8226c91d07",
    "txaction" => "appointed",
    "portalid" => "2025315",
    "aid" => "35249",
    "clearingtype" => "wlt",
    "notify_version" => "7.4",
    "txtime" => "1492030322",
    "currency" => "EUR",
    "userid" => "99757919",
    "accessname" => "",
    "accesscode" => "",
    "param" => "",
    "mode" => "live",
    "price" => "50.00",
    "id" => array("1" => "1042"),
    "pr" => array("1" => "50.00"),
    "no" => array("1" => "1"),
    "de" => array("1" => "Betriebswirtschaftslehre Basic"),
    "ti" => array("1" => "Betriebswirtschaftslehre Basic"),
    "va" => array("1" => "19.0"),
    "txid" => "221526821",
    "reference" => "457-58ee933b877f2",
    "sequencenumber" => "1",
    "company" => "",
    "firstname" => "Sebastian",
    "lastname" => "Bretzing",
    "street" => "",
    "zip" => "23560",
    "city" => "",
    "email" => "s.bretzing@gmx.de",
    "country" => "DE",
    "shipping_company" => "",
    "shipping_firstname" => "Bretzing",
    "shipping_lastname" => "Sebastian",
    "shipping_street" => "",
    "shipping_zip" => "23560",
    "shipping_city" => "",
    "shipping_country" => "DE",
    "customerid" => "sebretz",
    "transaction_status" => "completed",
    "balance" => "50",
    "receivable" => "50"
);


$query = http_build_query($response);

print $query;
*/