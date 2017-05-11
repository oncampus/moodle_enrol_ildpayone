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
 * Strings for component 'enrol_ildpayone', language 'en'.
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Payone';
$string['pluginname_desc'] = 'The PAYONE module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.';
$string['portalid'] = 'Portal-ID';
$string['mid'] = 'Merchant-ID';
$string['aid'] = 'Sub-Account-ID';
$string['portalkey'] = 'Key';
$string['api_version'] = 'API-Version';
$string['mode'] = 'Mode';
$string['mode_help'] = 'Which mode should be used? Live or test.';
$string['clearing_types'] = 'Payment method';
$string['clearing_types_help'] = 'Select available payment methods';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['ildpayone:config'] = 'Configure Payone enrol instances';
$string['ildpayone:manage'] = 'Manage enrolled users';
$string['ildpayone:unenrol'] = 'Unenrol users from course';
$string['ildpayone:unenrolself'] = 'Unenrol self from the course';
$string['frontend_url'] = 'Frontend-URL';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['cost'] = 'Cost: ';
$string['status'] = 'Allow Payone enrolments';
$string['status_desc'] = 'Allow users to use Payone to enrol into a course by default.';
$string['currency'] = 'Currency';
$string['assignrole'] = 'Assign role';
$string['cost_center'] = 'Cost center';
$string['product_id'] = 'Product-ID';
$string['paymentrequired'] = 'This course is fee required. You can pay with credit card, PayPal or via online bank transfer. After processing your payment, you get immediate access to the course.';
$string['paymentsuccess'] = 'Thank you for your payment. You get immediate access to the course. Upon successfully completing a quiz, you will receive a certificate of participation.<br/><br/>';
$string['continue'] = 'Continue to course';

$string['elv'] = 'Debit payment'; // Debit payment
$string['cc'] = 'Credit card'; // Credit card
$string['vor'] = 'Prepayment'; // Prepayment
$string['rec'] = 'Invoice'; // Invoice
$string['cod'] = 'Cash on delivery'; // Cash on delivery
$string['sb'] = 'Online bank transfer'; // Online bank transfer
$string['wlt'] = 'PayPal'; // e-Wallet
