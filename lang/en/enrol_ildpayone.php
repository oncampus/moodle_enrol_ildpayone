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

$string['cart'] = 'Cart';
$string['cart-additional'] = '<h4>Finished shopping?</h4><p>Most of the courses can be accessed directly after finishing the payment process by clicking on „Checkout“. You can then start learning immediately!
For all other courses click on „Inquiry“ and enter your data. You will then receive an e-mail with the contract that you need to sign and return to us. Then, we will send you a bill that needs to be paid in monthly rates. We will then unlock you for the term of your course.</p>';
$string['cart-additional-products'] = '<h4>Finished shopping?</h4><p>Great! Just click on „Checkout“, enter your payment details and complete the ordering process. You will then receive a confirmation e-mail and can access the course immediately. Have fun learning!</p>';
$string['cart-additional-zfu'] = '<h4>Finished shopping?</h4><p>Great! Just click on „Inquiry“ and enter your data. You will then receive an e-mail with the contract that you need to sign and return to us. Then, we will send you a bill that needs to be paid in monthly rates. We will then unlock you for the term of your course.</p>';
$string['inquiry'] = 'Inquiry';
$string['product'] = 'Product';
$string['price'] = 'Price';
$string['qty'] = 'Quantity';
$string['subtotal'] = 'Subtotal';
$string['netto'] = 'Net';
$string['vat'] = 'incl. {$a}% VAT';
$string['total'] = 'Final Amount';
$string['continue-shopping'] = 'Continue Shopping';
$string['goto-checkout'] = 'Checkout';
$string['goto-inquiry'] = 'Inquiry';
$string['coupon'] = 'Discount code: ';
$string['code'] = 'Code: ';
$string['redeemed-coupon'] = '{$a->code} - {$a->discount} discount';
$string['graduation-price'] = 'Staffelpreis - {$a->discountValue}% Rabatt ab {$a->conditionValue} Stück.';
$string['as-coupon'] = 'I would like to buy the course for another person';
$string['as-coupon_help'] = 'You do not want to buy this course for yourself, but for another person? Then click this box.';
$string['invalid-coupon-code'] = 'Ups, ein schwarzes Loch! Dieser Rabatt-Code existiert leider nicht. Bitte prüfe deine Eingabe oder wende dich an unseren Support.';
$string['valid-coupon-code'] = 'Mission geschafft! Dein Rabatt-Code wurde erfolgreich eingelöst.';

$string['salutation'] = 'Salutation';
$string['zip'] = 'Zipcode';
$string['birthday'] = 'Birthday';
$string['birth-city'] = 'Place of birth';

$string['company'] = 'Company';
$string['invoice'] = 'Different billing address';

$string['send-requests'] = 'Inquiry/Free Access Anfragen an Moodalis';

$string['free-access'] = 'Vielen Dank für deine Buchung bei oncampus. Du hast den 100 % Rabatt für unsere Integrations-Kurse erfolgreich eingelöst. Du kannst gleich in den Kurs gehen oder noch weiter in unserem Angebot stöbern.';
$string['thx'] = 'Vielen Dank für deine Buchung bei oncampus. Die Buchung hat funktioniert. Wir freuen uns, dass du in unserer World of Learning fündig geworden bist.  Du kannst gleich in den Kurs gehen oder noch weiter in unserem Angebot stöbern.';
$string['thx-zfu'] = 'Vielen Dank für deine Anfrage bei oncampus. Deine Anfrage ist bei uns angekommen. Wir freuen uns, dass du in unserer World of Learning fündig geworden bist. Du erhältst in Kürze eine E-Mail mit dem Vertrag. Sollte die E-Mail nicht ankommen, checke bitte auch deinen Spam-Ordner. Schicke den Vertrag bitte unterschrieben an uns zurück. Danach steht deiner Teilnahme an den Kursen nichts mehr im Weg.';

$string['vat_value'] = "VAT";
$string['testuser'] = 'Testuser';