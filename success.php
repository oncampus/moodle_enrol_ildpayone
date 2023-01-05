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
 * Listens for Payment Notification from Payone
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

require_once('../../config.php');

global $USER, $OUTPUT, $PAGE, $CFG, $DB, $SESSION;

require_once($CFG->libdir . '/enrollib.php');
require_once('locallib.php');
require_once($CFG->dirroot . '/enrol/ildpayone/classes/cart.php');

$user = required_param('user', PARAM_INT);
$hash = required_param('hash', PARAM_TEXT);
$zfu = optional_param('zfu', '', PARAM_INT);

$cart = new \ildpayone\cart\Cart();
$hash_string = $user . $cart->get_id();
$check_hash = hash_hmac("sha384", $hash_string, get_config('enrol_ildpayone', 'portalkey'));

if ($hash != $check_hash) {
    die("Not a valid hash");
}

if ($user != $USER->id) {
    die("Not a valid user id");
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_title('Paywall');

$plugin = enrol_get_plugin('ildpayone');

if(!empty($zfu)) {
    $products = $cart->get_zfu_products();
    $free_access = ($cart->get_zfu_total() == 0) ? true : false;
} else {
    $products = $cart->get_products();
    $free_access = ($cart->get_total() == 0) ? true : false;
}

$serve_as_coupons = ($cart->get_ascoupons() == 1) ? true : false;

if (!$free_access) {
    // Facebook pixel & Google analytics tracking
    $fb_script_tag = '<script>fbq("track", "Purchase", {contents: [';
    $ga_script_tag = '<script>ga("require", "ec");ga("set", "currencyCode", "EUR");';

    foreach ($products as $id => $product) {
        $fb_script_tag .= '{"id": "' . $product->get_productid() . '", "quantity": ' . $product->get_quantity() . ', "item_price": ' . $product->get_price() . '},';

        $ga_script_tag .= 'ga("ec:addProduct",{"id":"' . $product->get_productid()
            . '", "name":"' . $product->get_title()
            . '", "category":"' . get_string('tracking_category_' . $product->get_type(), 'block_ocproducts')
            . '", "price":"' . $product->get_price()
            . '", "quantity":"' . $product->get_quantity() . '"});';
    }

    if ($cart->has_discount()) {
        $value = $cart->get_total() - $cart->get_discount()->discountValue;
    } else {
        $value = $cart->get_total();
    }

    $fb_script_tag .= '], content_type: "product", value: ' . $value . ', currency: "EUR"});</script>';
    $ga_script_tag .= 'ga("ec:setAction", "purchase", {"id":"' . $cart->get_id()
        . '", "revenue":"' . $value . '"});ga("send", "pageview");
    ga("send", "event", "ecommerce", "purchased", "Kauf erfolgreich");';

    $ga_script_tag .= "gtag('event', 'conversion', {
        'send_to': 'AW-988857445/Z2rZCO7b7OEBEOWIw9cD',
        'transaction_id': ''
    });
  </script>";
}

if (!$serve_as_coupons) {
    foreach ($cart->get_products() as $product) {
        if ($product->get_quantity() > 1) {
            $serve_as_coupons = true;
            continue;
        }
    }
}

if ($serve_as_coupons) {
    require_once($CFG->dirroot . '/blocks/coupon/externallib.php');

    $generatesinglepdfs = false;
    $coupons = new block_coupon_external();
    $codes = array();

    foreach ($products as $product) {
        $couponcourses = array($product->get_courseid());
        $groups = null;

        $generatedcodes = $coupons->request_coupon_codes_for_course($product->get_quantity(), $couponcourses, $groups);
        $codes = array_merge($codes, $generatedcodes);

        $cart->delete($product->get_id());
    }

    $generatedcoupons = $DB->get_records_list('block_coupon', 'submission_code', $codes);
    $return = block_coupon\helper::mail_coupons($generatedcoupons, $USER->email, $generatesinglepdfs);
} else {
    foreach ($products as $product) {
        if (!$plugin_instance = $DB->get_record("enrol", array("id" => $product->get_enrol_instance(), "status" => 0))) {
            die("Not a valid instance id");
        }

        if (!$context = context_course::instance($plugin_instance->courseid, IGNORE_MISSING)) {
            die("Not a valid context id");
        }

        if ($plugin_instance->enrolperiod) {
            $timestart = time();
            $timeend = $timestart + $plugin_instance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend = 0;
        }
        $plugin->enrol_user($plugin_instance, $user, $plugin_instance->roleid, $timestart, $timeend);

        if (($system = $product->get_external_registration_system()) != '0') {
            require_once($CFG->dirroot . '/oncampus/external_systems/external_system.php');
            external_system_create_user($system);
        }

        $cart->delete($product->get_id());
    }
}

echo $OUTPUT->header();
echo $OUTPUT->box_start();

if ($free_access) {
    echo get_string('free-access', 'enrol_ildpayone') . '<br/><a href="' . $CFG->httpswwwroot . '/my">Zum Dashboard</a>';
} else if (!$cart->is_empty()) {
    echo get_string('thx', 'enrol_ildpayone') . '<br/>';
    echo 'Du hast noch offene Anfragen! <a href="' . $CFG->httpswwwroot . '/enrol/?id=' . $cart->get_enrolid() . '">Zu den Anfragen</a>';
} else {
    echo get_string('thx', 'enrol_ildpayone') . '<br/><a href="' . $CFG->httpswwwroot . '/my">Zum Dashboard</a>';
}

if (isset($fb_script_tag)) {
    echo $fb_script_tag;
}

if (isset($ga_script_tag)) {
    echo $ga_script_tag;
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();