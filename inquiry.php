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
 * Inquiry Site
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $USER, $OUTPUT, $PAGE, $CFG, $DB, $SESSION;

require_once($CFG->dirroot . '/enrol/ildpayone/classes/cart.php');
require_once($CFG->dirroot . '/enrol/ildpayone/inquiry_form.php');
require_once($CFG->dirroot . '/enrol/ildpayone/locallib.php');

if (isguestuser()) {
    redirect(get_login_url());
}

$cartid = optional_param('cartid', '', PARAM_TEXT);

$cart = new \ildpayone\cart\Cart();

if (empty($cartid) || $cartid != $cart->get_id()) {
    redirect(new moodle_url('/'));
}

$PAGE->https_required();

$PAGE->set_url('/enrol/ildpayone/inquiry.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('Inquiry');

$inquiry_form = new ildpayone_inquiry_form(null, array('cartid' => $cartid));

if ($inquiry_form->is_cancelled()) {
    redirect(new moodle_url('/enrol/?id=' . $cart->get_enrolid()));
} else if ($fromform = $inquiry_form->get_data()) {

    $created = ildpayone_create_inquiry($fromform);

    if ($created) {
        $zfu_products = $cart->get_zfu_products();

        foreach ($zfu_products as $id => $product) {
            $cart->delete($id);
        }

        $output = get_string('thx-zfu', 'enrol_ildpayone');
    } else {
        $output = 'Deine Anfrage konnte nicht bearbeitet werden. Bitte versuche es erneut oder kontaktiere den Support!';
    }
}

echo $OUTPUT->header();
echo $OUTPUT->box_start();

echo '<div id="page-coursesiteheader" class="row">
    <div class="coursesiteheader col-xs-12 col-sm-9 col-md-9">
        <h1>Fast geschafft...</h1>
    </div>
    <div class="courseimage col-xs-12 col-sm-3 col-md-3">
        <div class="ocproduct-overview-img overview-enrol"></div>
    </div>
</div>
<div class="clearfix">
</div>';

if (isset($output) && !empty($output)) {
    echo $output;
} else {
    $inquiry_form->display();
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();