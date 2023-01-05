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
 * Cart class
 *
 * @package    enrol_ildpayone
 * @copyright  2016 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ildpayone\cart;

global $CFG;
require_once($CFG->dirroot . '/enrol/ildpayone/classes/product.php');

class Cart implements \Countable {
    private $id;
    private $products;
    private $discount;
    private $zfu_discount;
    private $total;
    private $zfu_total;
    private $vat;
    private $vat_rate;
    private $vat_rate_zfu;
    private $enrolid;
    private $ascoupons;

    public function __construct() {
        $this->products = array();
        $this->vat = get_config('enrol_ildpayone', 'vat');
        $this->enrolid = array();
        $this->ascoupons = 0;
        $this->total = 0;
        $this->zfu_total = 0;
        $this->vat_rate = 0;
        $this->vat_rate_zfu = 0;

        $this->load();
    }

    public function add(Product $product) {
        $id = $product->get_id();

        if (!isset($this->products[$id])) {
            $this->products[$id] = $product;
            $courseid = $product->get_courseid();
            $this->enrolid[$courseid] = $courseid;
        }

        $this->save();
    }

    public function update($productid, $action, $qty = null) {
        if (isset($this->products[$productid])) {
            switch ($action) {
                case 'incr':
                    $this->products[$productid]->incr_quantity();
                    break;
                case 'decr':
                    $this->products[$productid]->decr_quantity();
                    break;
                case 'update':
                    $this->products[$productid]->set_quantity($qty);
                    break;
            }

            $this->save();
        }
    }

    public function delete($productid) {
        global $SESSION;

        if (isset($this->products[$productid])) {
            $product = $this->products[$productid];
            $enrolid = $product->get_courseid();
            unset($this->enrolid[$enrolid]);
            unset($this->products[$productid]);

            if ($this->is_empty()) {
                unset($SESSION->cart);
            } else {
                $this->save();

                if (isset($this->total) && $this->total == 0) {
                    unset($this->discount);
                }

                if (isset($this->zfu_total) && $this->zfu_total == 0) {
                    unset($this->zfu_discount);
                }

                $this->save();
            }
        }
    }

    public function has_product($productid) {
        return (isset($this->products[$productid])) ? true : false;
    }

    public function has_discount() {
        return (isset($this->discount)) ? true : false;
    }

    public function has_zfu_discount() {
        return (isset($this->zfu_discount)) ? true : false;
    }

    public function is_empty() {
        return (empty($this->products));
    }

    private function load() {
        global $SESSION, $DB;

        if (isset($SESSION->cart)) {
            $session_cart = unserialize($SESSION->cart);

            if (isset($session_cart->id)) {
                $this->id = $session_cart->id;
            }

            if (isset($session_cart->discount)) {
                $this->discount = $session_cart->discount;
            }

            if (isset($session_cart->zfu_discount)) {
                $this->zfu_discount = $session_cart->zfu_discount;
            }

            if (isset($session_cart->ascoupons)) {
                $this->ascoupons = $session_cart->ascoupons;
            }

            foreach ($session_cart->products as $product) {
                $compare_clause = $DB->sql_compare_text('product') . ' = ' . $DB->sql_compare_text(':id');
                $ocproduct = $DB->get_record_sql("select * from {block_ocproducts} where $compare_clause", array('id' =>
                    $product->id));

                $cart_product = new \ildpayone\cart\Product($ocproduct, $product->enrol_instance);
                $cart_product->set_quantity($product->qty);

                if (isset($product->discount)) {
                    $cart_product->set_discount($product->discount);
                }

                $this->add($cart_product);
            }
        }
    }

    private function calc_total() {
        $this->total = 0;
        $this->zfu_total = 0;

        foreach ($this->products as $product) {
            if ($product->get_type() == 'zfu_course') {
                $this->zfu_total += ($product->get_price() * $product->get_quantity());
            } else {
                $this->total += ($product->get_price() * $product->get_quantity());
            }
        }
        $this->calc_vat();
    }

    private function calc_vat() {
        $this->vat_rate = 0;
        $this->vat_rate_zfu = 0;

        if (isset($this->discount)) {
            $total = $this->total - $this->discount->discountValue;
            $subtotal = round(($total / (1 + ($this->vat / 100))), 2);
            $this->vat_rate = round($total - $subtotal, 2);

            if (isset($this->zfu_discount)) {
                $zfu_total = $this->zfu_total - $this->zfu_discount->discountValue;
                $zfu_subtotal = round(($zfu_total / (1 + ($this->vat / 100))), 2);
                $this->vat_rate_zfu = round($zfu_total - $zfu_subtotal, 2);
            }
        } else {
            $subtotal = round(($this->total / (1 + ($this->vat / 100))), 2);
            $this->vat_rate = round($this->total - $subtotal, 2);

            $zfu_subtotal = round(($this->zfu_total / (1 + ($this->vat / 100))), 2);
            $this->vat_rate_zfu = round($this->zfu_total - $zfu_subtotal, 2);
        }
    }

    private function save() {
        global $SESSION;

        $cart = new \stdClass();
        foreach ($this->products as $product) {
            $cart->products[] = $product->to_session();
        }

        if (isset($this->discount) && $this->discount != null) {
            $cart->discount = $this->discount;
        }

        if (isset($this->zfu_discount) && $this->zfu_discount != null) {
            $cart->zfu_discount = $this->zfu_discount;
        }

        if (isset($this->ascoupons) && $this->ascoupons != null) {
            $cart->ascoupons = $this->ascoupons;
        }

        if (isset($this->id)) {
            $cart->id = $this->id;
        } else {
            $cart->id = uniqid('ildcart');
        }

        $cart->enrolid = $this->enrolid;

        $SESSION->cart = serialize($cart);
        $this->calc_total();
    }

    public function show() {
        global $CFG, $SESSION, $USER;

        $products = $this->get_products();
        $zfu = $this->get_zfu_products();
        $abs_discount = $this->discount;
        $zfu_abs_discount = $this->zfu_discount;
        $total = $this->total;
        $zfu_total = $this->zfu_total;
        $vat_rate = $this->vat_rate;
        $vat_rate_zfu = $this->vat_rate_zfu;
        $enrolid = $this->get_enrolid();
        $cart_id = $this->id;
        $ascoupon = ($this->ascoupons == 1) ? 'checked="checked"' : '';

        if (isset($abs_discount) && (($total - $abs_discount->discountValue) < 0)) {
            $cart_total = 0;
            $vat_rate = 0;
        } else if (isset($abs_discount)) {
            $cart_total = $total - $abs_discount->discountValue;
        }


        if (isset($zfu_abs_discount) && (($total - $zfu_abs_discount->discountValue) < 0)) {
            $cart_zfu_total = 0;
            $vat_rate_zfu = 0;
        } else if (isset($zfu_abs_discount)) {
            $cart_zfu_total = $zfu_total - $zfu_abs_discount->discountValue;
        }

        ob_start();
        include($CFG->dirroot . '/enrol/ildpayone/cart.html');

        return ob_get_clean();
    }

    public function redeem_coupon($code) {
        $absolute_discount = array();
        $zfu_absolute_discount = array();
        $absolute_discount_value = 0;
        $zfu_absolute_discount_value = 0;
        $coupon_found = true;
        $coupon_found_zfu = true;

        foreach ($this->products as $product) {
            #$has_absolute_discount = $product->redeem_coupon($code);
            $has_discounts = $product->redeem_coupon($code);

            if (!empty($has_discounts['absolute'])) {
                if ($product->get_type() != 'zfu_course') {
                    array_push($absolute_discount, $has_discounts['absolute']);
                } else {
                    array_push($zfu_absolute_discount, $has_discounts['absolute']);
                }
            }
        }

        if (!empty($absolute_discount)) {
            foreach ($absolute_discount as $discount) {
                if ($discount->discountValue > $absolute_discount_value) {
                    $absolute_discount_value = $discount->discountValue;
                    $this->discount = $discount;
                }
            }
        } else {
            $coupon_found = false;
        }

        if (!empty($zfu_absolute_discount)) {
            foreach ($zfu_absolute_discount as $discount) {
                if ($discount->discountValue > $zfu_absolute_discount_value) {
                    $zfu_absolute_discount_value = $discount->discountValue;
                    $this->zfu_discount = $discount;
                }
            }
        } else {
            $coupon_found_zfu = false;
        }

        $this->save();

        if (($coupon_found) || ($coupon_found_zfu) || !empty($has_discounts['percentage'])) {
            return true;
        } else {
            return false;
        }
    }

    public function checkout() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/enrol/ildpayone/locallib.php');

        $payments = ildpayone_prepare_purchase();
        $products = $this->get_products();
        $logo_url = $CFG->httpswwwroot . '/enrol/ildpayone/logos/';
        $iframe_height = array('cc' => 1250, 'sb' => 1400, 'wlt' => 1100);
        $logos = array(
            'cc' => '<span style="float:right"><img style="padding-right: 10px;" src="' . $logo_url . 'visa.png" alt="Visa"><img style="padding-right: 10px;" src="' . $logo_url . 'mc.png" alt="MasterCard"><img src="' . $logo_url . 'maestro.jpg" alt="Maestro"></span>',
            'sb' => '<span style="float:right"><img style="padding-right: 10px;" src="' . $logo_url . 'giropay.png" alt="Giropay"><img src="' . $logo_url . 'sofort.png" alt="Sofort Ueberweisung"></span>',
            'wlt' => '<span style="float:right"><img src="' . $logo_url . 'paypal.png" alt="PayPal"></span>'
        );

        $ga_tracking = '<script>ga("require", "ec");';
        foreach ($products as $id => $product) {
            $ga_tracking .= 'ga("ec:addProduct",{"id":"' . $product->get_productid()
                . '", "name":"' . $product->get_title()
                . '", "category":"' . get_string('tracking_category_' . $product->get_type(), 'block_ocproducts')
                . '", "price":"' . $product->get_price()
                . '", "quantity":"' . $product->get_quantity() . '"});';
        }
        $ga_tracking .= 'ga("ec:setAction", "checkout", {"step":1});ga("send", "pageview");';
        
        $ga_tracking .= 'ga("send", "event", "ecommerce", "begin_checkout", "Zur Kasse");</script>';

        ob_start();
        include($CFG->dirroot . '/enrol/ildpayone/checkout.html');

        return ob_get_clean();
    }

    public function get_enrolid() {
        reset($this->enrolid);
        $key = key($this->enrolid);
        return $this->enrolid[$key];
    }

    public function get_products() {
        $products = array();
        foreach ($this->products as $id => $product) {
            if ($product->get_type() != 'zfu_course') {
                $products[$id] = $product;
            }
        }

        return $products;
    }

    public function get_zfu_products() {
        $zfu = array();
        foreach ($this->products as $id => $product) {
            if ($product->get_type() == 'zfu_course') {
                $zfu[$id] = $product;
            }
        }

        return $zfu;
    }

    public function get_vat() {
        return $this->vat;
    }

    public function get_discount() {
        return $this->discount;
    }

    public function get_zfu_discount() {
        return $this->zfu_discount;
    }

    public function get_total() {
        return $this->total;
    }

    public function get_zfu_total() {
        return $this->zfu_total;
    }

    public function get_id() {
        return $this->id;
    }

    public function count() {
        return count($this->products);
    }

    public function set_ascoupons($ascoupon) {
        if ($ascoupon == 'on') {
            $this->ascoupons = 1;
        } else {
            $this->ascoupons = 0;
        }

        $this->save();
    }

    public function get_ascoupons() {
        return $this->ascoupons;
    }
}