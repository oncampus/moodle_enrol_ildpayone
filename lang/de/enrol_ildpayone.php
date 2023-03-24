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

$string['cart'] = 'Warenkorb';
$string['cart-additional'] = '<h4>Fertig geshoppt?</h4><p>Auf die meisten Kurse kannst du direkt nach Abschluss des Zahlungsvorgangs unter „Zur Kasse“ zugreifen und so direkt mit dem Lernen starten. Für alle betreuten Kurse gib bitte unter „Zur Anfrage“ deine Daten ein. Du erhältst dann eine E-Mail mit dem Vertrag von uns, den du unterschrieben an uns zurück schickst. Im nächsten Schritt schicken wir dir eine Rechnung, die in monatlichen Raten beglichen werden muss. Wir schalten dich dann zur entsprechenden Kurslaufzeit für den Kurs frei!</p>';
$string['cart-additional-products'] = '<h4>Fertig geshoppt?</h4><p>Super! Klicke einfach auf „Zur Kasse“,  gib deine Zahlungsdaten ein und schließe den Bestellvorgang ab. Im Anschluss erhältst du eine Bestätigungsmail und kannst direkt auf deinen gekauften Kurs zugreifen. Wir wünschen dir viel Spaß beim Lernen!</p>';
$string['cart-additional-zfu'] = '<h4>Fertig geshoppt?</h4><p>Super! Klicke einfach auf „Zur Anfrage“ und gib deine Daten ein. Du erhältst dann eine E-Mail mit dem Vertrag, den du unterschrieben an uns zurück schickst. Im nächsten Schritt schicken wir dir eine Rechnung, die in monatlichen Raten beglichen werden muss. Wir schalten dich dann zur entsprechenden Kurslaufzeit für den Kurs frei!</p>';
$string['inquiry'] = 'Anfrage';
$string['product'] = 'Produkt';
$string['price'] = 'Preis';
$string['qty'] = 'Menge';
$string['subtotal'] = 'Nettobetrag';
$string['netto'] = 'Endpreis';
$string['vat'] = 'inkl. {$a}% USt.';
$string['total'] = 'Bruttobetrag';
$string['continue-shopping'] = 'Weiter Einkaufen';
$string['goto-checkout'] = 'Zur Kasse';
$string['goto-inquiry'] = 'Zur Anfrage';
$string['coupon'] = 'Rabatt-Code: ';
$string['code'] = 'Code: ';
$string['redeemed-coupon'] = '{$a->code} - {$a->discount} Rabatt';
$string['graduation-price'] = 'Staffelpreis - {$a->discountValue}% Rabatt ab {$a->conditionValue} Stück.';
$string['as-coupon'] = 'Ich möchte den Kurs für eine andere Person kaufen.';
$string['as-coupon_help'] = 'Du möchtest diesen Kurs nicht für dich, sondern für eine andere Person kaufen? Dann klicke dieses Feld an.';
$string['invalid-coupon-code'] = 'Ups, ein schwarzes Loch! Dieser Rabatt-Code existiert leider nicht. Bitte prüfe deine Eingabe oder wende dich an unseren Support.';
$string['valid-coupon-code'] = 'Mission geschafft! Dein Rabatt-Code wurde erfolgreich eingelöst.';

$string['salutation'] = 'Anrede';
$string['zip'] = 'PLZ';
$string['birthday'] = 'Geburtstag';
$string['birth-city'] = 'Geburtsort';
$string['company'] = 'Firma';
$string['invoice'] = 'Abweichende Rechnungsinformationen';
$string['invoice_email'] = 'E-mail Adresse (wenn abweichend)';
$string['invoice_email_prompt'] = 'E-mail Adresse für den Rechnungsversand. Wenn nicht gesetztwird die Rechnung an die im Nutzerprofil gespeicherte Adresse versandt.';

$string['order_number'] = 'Auftragsnummer (optional)';

$string['send-requests'] = 'Inquiry/Free Access Anfragen an Moodalis';

$string['free-access'] = 'Vielen Dank für deine Buchung bei oncampus. Du hast den 100%-Rabatt für diesen Kurs erfolgreich eingelöst. Du kannst gleich in den Kurs gehen oder noch weiter in unserem Angebot stöbern.';
$string['thx'] = 'Vielen Dank für deine Buchung bei oncampus. Die Buchung hat funktioniert. Wir freuen uns, dass du in unserer World of Learning fündig geworden bist.  Du kannst gleich in den Kurs gehen oder noch weiter in unserem Angebot stöbern.';
$string['thx-zfu'] = 'Vielen Dank für deine Anfrage bei oncampus. Deine Anfrage ist bei uns angekommen. Wir freuen uns, dass du in unserer World of Learning fündig geworden bist. Du erhältst in Kürze eine E-Mail mit dem Vertrag. Sollte die E-Mail nicht ankommen, checke bitte auch deinen Spam-Ordner. Schicke den Vertrag bitte unterschrieben an uns zurück. Danach steht deiner Teilnahme an den Kursen nichts mehr im Weg.';

$string['vat_value'] = "USt.";
$string['testuser'] = 'Testnutzer';