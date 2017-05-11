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
require_once($CFG->libdir . '/enrollib.php');
require_once('locallib.php');
global $USER, $OUTPUT, $PAGE;

$course = required_param('course', PARAM_INT);
$user = required_param('user', PARAM_INT);
$instance = required_param('instance', PARAM_INT);
$hash = required_param('hash', PARAM_TEXT);

$hash_string = $course . $user . $instance;
$check_hash = hash_hmac("sha384", $hash_string, get_config('enrol_ildpayone', 'portalkey'));

if ($hash != $check_hash) {
    die("Not a valid hash");
}

if ($user != $USER->id) {
    die("Not a valid user id");
}

if (!$context = context_course::instance($course, IGNORE_MISSING)) {
    die("Not a valid context id");
}

if (!$plugin_instance = $DB->get_record("enrol", array("id" => $instance, "status" => 0))) {
    die("Not a valid instance id");
}

$url = new \moodle_url('/enrol/ildpayone/success.php', array('course' => $course, 'user' => $user, 'instance' => $instance, 'hash' => $hash));
$PAGE->set_url($url);
$PAGE->set_title('Paywall');

$plugin = enrol_get_plugin('ildpayone');

if ($plugin_instance->enrolperiod) {
    $timestart = time();
    $timeend = $timestart + $plugin_instance->enrolperiod;
} else {
    $timestart = 0;
    $timeend = 0;
}

// Enrol user
$plugin->enrol_user($plugin_instance, $user, $plugin_instance->roleid, $timestart, $timeend);

echo $OUTPUT->header();
echo $OUTPUT->box_start();
echo get_string('paymentsuccess', 'enrol_ildpayone') . '<br/><a href="' . $CFG->httpswwwroot . '/course/view.php?id=' . $course . '">' . get_string('continue', 'enrol_ildpayone') . '</a>';
echo $OUTPUT->box_end();
echo $OUTPUT->footer();