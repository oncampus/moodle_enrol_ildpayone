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
 * Cart product class
 *
 * @package    enrol_ildpayone
 * @copyright  2016 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ildpayone\cart;

class Product {
    private $id;
    private $data;
    private $title;
    private $price;
    private $quantity;
    private $discount;
    private $graduation_discount;
    private $courseid;
    private $enrol_instance;
    private $external_registration_system;

    public function __construct($ocproduct, $enrol_instance) {
        $this->id = $ocproduct->product;
        $this->data = json_decode($ocproduct->json);
        $this->title = \format_string($ocproduct->title);
        $this->price = $this->data->attributes->offers[0]->price;
        $this->quantity = 1;
        $this->courseid = $ocproduct->course;
        $this->enrol_instance = $enrol_instance;

        if (isset($this->data->attributes->external_registration_system)) {
            $this->external_registration_system = $this->data->attributes->external_registration_system;
        } else {
            $this->external_registration_system = 0;
        }
    }

    public function to_session() {
        $data = new \stdClass();
        $data->id = $this->id;
        $data->qty = $this->quantity;

        if (isset($this->discount)) {
            $data->discount = $this->discount->id;
        }

        if (isset($this->graduation_discount)) {
            $data->graduation_discount = $this->graduation_discount->id;
        }

        $data->enrol_instance = $this->enrol_instance;

        return $data;
    }

    public function redeem_coupon($code) {
        $discounts = $this->data->attributes->discounts;
        $absolute_discount_value = 0;
        $absolute_discount = null;
        $has_discounts = array();

        if (isset($this->discount)) {
            $discount_value = $this->discount->discountValue;
        } else {
            $discount_value = 0;
        }

        foreach ($discounts as $discount) {
            if (mb_strtolower($discount->code) == mb_strtolower($code) && $discount->discountType == 'discount_type_percent' && $discount->discountValue > $discount_value) {
                $starttime = strtotime($discount->start);
                $endtime = strtotime($discount->end);
                $now = time();

                if ($now >= $starttime && $now <= $endtime) {
                    $this->discount = $discount;
                    $discount_value = $this->discount->discountValue;
                    $has_discounts['percentage'] = $discount;
                }
            }

            if (mb_strtolower($discount->code) == mb_strtolower($code) && $discount->discountType == 'discount_type_absolute' && $discount->discountValue > $absolute_discount_value) {
                $starttime = strtotime($discount->start);
                $endtime = strtotime($discount->end);
                $now = time();

                if ($now >= $starttime && $now <= $endtime) {
                    $has_discounts['absolute'] = $discount;
                    $absolute_discount_value = $discount->discountValue;
                }
            }
        }

        if (isset($this->discount) && $this->discount->discountValue == 100) {
            $this->set_quantity(1);
        }

        $this->calc_price();

        return $has_discounts;
    }

    private function calc_price() {
        if (isset($this->discount)) {
            $this->price = $this->price - ($this->price * ($this->discount->discountValue / 100));
        } else if (isset($this->graduation_discount)) {
            $this->price = $this->price - ($this->price * ($this->graduation_discount->discountValue / 100));
        }
    }

    public function get_title() {
        return $this->title;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_productid() {
        return $this->data->attributes->offers[0]->productID;
    }

    public function get_tax_rate() {
        return $this->data->attributes->offers[0]->tax_rate;
    }

    public function get_courseid() {
        return $this->courseid;
    }

    public function get_thumbnail() {
        return $this->data->attributes->productImage;
    }

    public function get_type() {
        return $this->data->type;
    }

    public function get_original_price() {
        return $this->data->attributes->offers[0]->price;
    }

    public function get_price() {
        return $this->price;
    }

    public function get_quantity() {
        return $this->quantity;
    }

    public function set_quantity($quantity) {
        if (isset($this->discount) && $this->discount->discountValue == 100) {
            $quantity = 1;
        }

        if ($quantity > 0) {
            $this->quantity = $quantity;
            $this->check_graduation_discount($this->quantity);
        }
    }

    public function incr_quantity() {
        if (isset($this->discount) && $this->discount->discountValue == 100) {
            $this->quantity = 1;
        } else {
            $this->quantity++;
        }

        $this->check_graduation_discount($this->quantity);
    }

    public function decr_quantity() {
        if ($this->quantity > 1) {
            if (isset($this->discount_value) && $this->discount_value == 100) {
                $this->quantity = 1;
            } else {
                $this->quantity--;
            }

            $this->check_graduation_discount($this->quantity);
        }
    }

    private function check_graduation_discount($quantity) {
        $discounts = $this->data->attributes->discounts;
        $highest_value = 0;

        foreach ($discounts as $discount) {
            if ($discount->discountType == 'discount_type_graduation_price') {
                foreach ($discount->discountConditions as $condition) {
                    if ($condition->conditionType == 'condition_type_number') {
                        if ($quantity >= $condition->conditionValue && $condition->conditionValue > $highest_value) {
                            $this->graduation_discount = $discount;
                            $highest_value = $condition->conditionValue;
                        }
                    }
                }
            }
        }

        $this->calc_price();
    }

    public function set_discount($id) {
        $discounts = $this->data->attributes->discounts;

        foreach ($discounts as $discount) {
            if ($discount->id == $id) {
                $this->discount = $discount;
                $this->calc_price();
            }
        }
    }

    public function get_discount() {
        return $this->discount;
    }

    public function has_discount() {
        return (isset($this->discount)) ? true : false;
    }

    public function has_graduation_discount() {
        return (isset($this->graduation_discount)) ? true : false;
    }

    public function get_graduation_discount() {
        return $this->graduation_discount;
    }

    public function get_enrol_instance() {
        return $this->enrol_instance;
    }

    public function get_data() {
        return $this->data;
    }

    public function get_external_registration_system() {
        return $this->external_registration_system;
    }
}