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

defined('MOODLE_INTERNAL') || die();

/**
 * Payone enrolment plugin implementation.
 * @author  Eugen Ebel - based on code by Martin Dougiamas and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_ildpayone_plugin extends enrol_plugin {

    public function get_currencies() {
        /*
        $codes = array(
            'AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY',
            'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'USD');
        */

        $codes = array(
            'EUR'
        );

        $currencies = array();
        foreach ($codes as $c) {
            $currencies[$c] = new lang_string($c, 'core_currencies');
        }

        return $currencies;
    }

    /**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $instances) {
        $found = false;
        foreach ($instances as $instance) {
            if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
                continue;
            }
            if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
                continue;
            }
            $found = true;
            break;
        }
        if ($found) {
            return array(new pix_icon('icon', get_string('pluginname', 'enrol_ildpayone'), 'enrol_ildpayone'));
        }
        return array();
    }

    public function roles_protected() {
        // users with role assign cap may tweak the roles later
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually - requires enrol/ildpayone:unenrol
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status - requires enrol/ildpayone:manage
        return true;
    }

    public function show_enrolme_link(stdClass $instance) {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Sets up navigation entries.
     *
     * @param object $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'ildpayone') {
            throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/ildpayone:config', $context)) {
            $managelink = new moodle_url('/enrol/ildpayone/edit.php', array('courseid' => $instance->courseid, 'id' => $instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'ildpayone') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/ildpayone:config', $context)) {
            $editlink = new moodle_url("/enrol/ildpayone/edit.php", array('courseid' => $instance->courseid, 'id' => $instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
                array('class' => 'iconsmall')));
        }

        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/ildpayone:config', $context)) {
            return NULL;
        }

        // multiple instances supported - different cost for different roles
        return new moodle_url('/enrol/ildpayone/edit.php', array('courseid' => $courseid));
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    function enrol_page_hook(stdClass $instance) {
        global $CFG, $USER, $OUTPUT, $PAGE, $DB, $SESSION;
        require_once($CFG->dirroot . '/enrol/ildpayone/locallib.php');
        require_once($CFG->dirroot . '/enrol/ildpayone/classes/cart.php');

        $PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/enrol/ildpayone/js/ildpayone.js'), false);
        $PAGE->force_theme('ild_oncampus');

        $action = optional_param('action', '', PARAM_TEXT);
        $productid = optional_param('product', '', PARAM_TEXT);
        $coupon = optional_param('coupon', '', PARAM_TEXT);
        $qty = optional_param('qty', '', PARAM_INT);
        $ascoupon = optional_param('ascoupon', '', PARAM_TEXT);

        $ocproduct = $DB->get_record('block_ocproducts', array('course' => $instance->courseid));
        $cart = new \ildpayone\cart\Cart();

        switch ($action) {
            case 'add':
                $cart->add(new \ildpayone\cart\Product($ocproduct, $instance->id));

                redirect($CFG->wwwroot . '/enrol/?id=' . $instance->courseid);
                break;

            case 'ascoupon':
                $cart->set_ascoupons($ascoupon);
                redirect($CFG->wwwroot . '/enrol/?id=' . $instance->courseid);
                break;

            case 'incr':
            case 'decr':
            case 'update':
                $cart->update($productid, $action, $qty);
                redirect($CFG->wwwroot . '/enrol/?id=' . $instance->courseid);
                break;

            case 'remove':
                $cart->delete($productid);

                if ($cart->is_empty()) {
                    redirect($CFG->wwwroot);
                } else {
                    redirect($CFG->wwwroot . '/enrol/?id=' . $cart->get_enrolid());
                }
                break;

            case 'checkout':
                if (isguestuser()) {
                    redirect(get_login_url());
                } else if (!$cart->is_empty() && (($cart->get_total() - $cart->get_discount()->discountValue) <= 0)) {
                    $hash_string = $USER->id . $cart->get_id();
                    $hash = hash_hmac("sha384", $hash_string, get_config('enrol_ildpayone', 'portalkey'));

                    ildpayone_create_free_access_invoice();
                    redirect($CFG->wwwroot . '/enrol/ildpayone/success.php?user=' . $USER->id . '&hash=' . $hash);
                } else {
                    return $OUTPUT->box($cart->checkout());
                }
                break;

            case 'coupon':
                if (isset($coupon) && !empty($coupon)) {
                    $coupon_found = $cart->redeem_coupon($coupon);

                    if (!$coupon_found) {
                        redirect($CFG->wwwroot . '/enrol/?id=' . $instance->courseid, get_string('invalid-coupon-code',
                            'enrol_ildpayone'), null, \core\output\notification::NOTIFY_ERROR);
                    } else {
                        redirect($CFG->wwwroot . '/enrol/?id=' . $instance->courseid, get_string('valid-coupon-code',
                            'enrol_ildpayone'), null, \core\output\notification::NOTIFY_INFO);
                    }
                }
                break;

            default:
                if ($cart->is_empty() || !$cart->has_product($ocproduct->product)) {
                    redirect($CFG->wwwroot . '/blocks/ocproducts/product.php?id=' . $ocproduct->product);
                }
                break;
        }

        return $OUTPUT->box($cart->show());
    }

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;
        if ($step->get_task()->get_target() == backup::TARGET_NEW_COURSE) {
            $merge = false;
        } else {
            $merge = array(
                'courseid' => $data->courseid,
                'enrol' => $this->get_name(),
                'roleid' => $data->roleid,
                'cost' => $data->cost,
                'currency' => $data->currency,
            );
        }
        if ($merge and $instances = $DB->get_records('enrol', $merge, 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol($instance) && has_capability("enrol/ildpayone:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class' => 'unenrollink', 'rel' => $ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/ildpayone:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class' => 'editenrollink', 'rel' => $ue->id));
        }
        return $actions;
    }

    public function cron() {
        $trace = new text_progress_trace();
        $this->process_expirations($trace);
    }

    /**
     * Execute synchronisation.
     * @param progress_trace $trace
     * @return int exit code, 0 means ok
     */
    public function sync(progress_trace $trace) {
        $this->process_expirations($trace);
        return 0;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/ildpayone:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/ildpayone:config', $context);
    }
}
