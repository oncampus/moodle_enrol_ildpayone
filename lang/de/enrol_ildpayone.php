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
 * Strings for component 'enrol_ildpayone', language 'de'.
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Payone';
$string['pluginname_desc'] = 'Das Plugin PAYONE erlaubt es, kostenpflichtige Kurse zu entwickeln. Wenn die Kosten für einen Kurs bei Null liegen, wird keine Zahlungsaufforderung für die Kurseinschreibung gezeigt.';
$string['portalid'] = 'Portal-ID';
$string['mid'] = 'Merchant-ID';
$string['aid'] = 'Sub-Account-ID';
$string['portalkey'] = 'Key';
$string['api_version'] = 'API-Version';
$string['mode'] = 'Modus';
$string['mode_help'] = 'In welchem Modus soll die PAYONE-Schnittstelle angesprochen werden.';
$string['clearing_types'] = 'Zahlarten';
$string['clearing_types_help'] = 'Auswahl der Zahlarten, die zur Verfügung stehen sollen.';
$string['expiredaction'] = 'Aktionen bei Ablauf der Kurseinschreibung';
$string['expiredaction_help'] = 'Wählen Sie die Aktionen, die beim Ablauf der Kurseinschreibung ausgeführt werden sollen. Beim Austragen des Nutzers aus dem Kurs werden einige Nutzerdaten und Einstellungen entfernt.';
$string['ildpayone:config'] = 'Configure Payone enrol instances';
$string['ildpayone:manage'] = 'Manage enrolled users';
$string['ildpayone:unenrol'] = 'Unenrol users from course';
$string['ildpayone:unenrolself'] = 'Unenrol self from the course';
$string['frontend_url'] = 'Frontend-URL';
$string['status'] = 'Payone-Einschreibung erlauben';
$string['cost_center'] = 'Kostenstelle';
$string['cost'] = 'Kosten: ';
$string['currency'] = 'Währung';
$string['assignrole'] = 'Rolle zuordnen';
$string['enrolperiod'] = 'Teilnahmedauer';
$string['enrolperiod_help'] = 'Teilnahmedauer (in Sekunden), beginnend mit dem Einschreibezeitpunkt. Falls dieser Wert auf Null gesetzt ist, ist die Teilnahmedauer standardmäßig unbegrenzt.';
$string['enrolstartdate'] = 'Einschreibebeginn';
$string['enrolstartdate_help'] = 'Wenn diese Option aktiviert ist, können Nutzer/innen ab diesem Zeitpunkt eingeschrieben werden.';
$string['enrolenddate'] = 'Einschreibeende';
$string['enrolenddate_help'] = 'Wenn diese Option aktiviert ist, können Nutzer/innen nur bis zu diesem Zeitpunkt eingeschrieben werden.';
$string['product_id'] = 'Produkt-ID';
$string['paymentrequired'] = 'Dieser Kurs ist kostenpflichtig. Du kannst ihn ganz bequem mit Kreditkarte, PayPal oder per Online-Überweisung bezahlen. Sobald du bezahlt hast, kannst du sofort auf alle Kursmaterialien zugreifen und loslegen.';
$string['paymentsuccess'] = 'Vielen Dank für deine Bezahlung. Du kannst ab sofort auf den Kurs zugreifen und dein Wissen vertiefen. Danach erlischt deine Zugriffsberechtigung automatisch. Im Anschluss erhältst du ein Weiterbildungszertifikat von oncampus, wenn du die gestellten Aufgaben erfolgreich gelöst hast.<br/><br/>Wir wünschen dir viel Spaß und Erfolg beim Lernen!<br/><br/>';
$string['continue'] = 'Weiter zum Kurs';

$string['elv'] = 'Lastschrift'; // Debit payment
$string['cc'] = 'Kreditkarte'; // Credit card
$string['vor'] = 'Vorkasse'; // Prepayment
$string['rec'] = 'Rechnung'; // Invoice
$string['cod'] = 'Nachname (DHL)'; // Cash on delivery
$string['sb'] = 'Online-Überweisung'; // Online bank transfer
$string['wlt'] = 'PayPal'; // e-Wallet