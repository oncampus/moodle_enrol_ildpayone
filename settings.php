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
 * Payone enrolments plugin settings and presets.
 *
 * @package    enrol_ildpayone
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    /* SETTINGS */
    $settings->add(new admin_setting_heading('enrol_ildpayone_settings', '', get_string('pluginname_desc', 'enrol_ildpayone')));

    $settings->add(new admin_setting_configtext('enrol_ildpayone/frontend_url', get_string('frontend_url', 'enrol_ildpayone'), '', '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('enrol_ildpayone/portalid', get_string('portalid', 'enrol_ildpayone'), '', '', PARAM_INT));
    $settings->add(new admin_setting_configtext('enrol_ildpayone/mid', get_string('mid', 'enrol_ildpayone'), '', '', PARAM_INT));
    $settings->add(new admin_setting_configtext('enrol_ildpayone/aid', get_string('aid', 'enrol_ildpayone'), '', '', PARAM_INT));
    $settings->add(new admin_setting_configtext('enrol_ildpayone/portalkey', get_string('portalkey', 'enrol_ildpayone'), '', '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('enrol_ildpayone/api_version', get_string('api_version', 'enrol_ildpayone'), '', '3.10', PARAM_RAW));
    $mode_options = array(
        'live' => 'live',
        'test' => 'test'
    );
    $settings->add(new admin_setting_configselect('enrol_ildpayone/mode', get_string('mode', 'enrol_ildpayone'), get_string('mode_help', 'enrol_ildpayone'), 'live', $mode_options));

    $clearing_types_options = array(
        'elv' => get_string('elv', 'enrol_ildpayone'), // Debit payment
        'cc' => get_string('cc', 'enrol_ildpayone'), // Credit card
        'vor' => get_string('vor', 'enrol_ildpayone'), // Prepayment
        'rec' => get_string('rec', 'enrol_ildpayone'), // Invoice
        'cod' => get_string('cod', 'enrol_ildpayone'), // Cash on delivery
        'sb' => get_string('sb', 'enrol_ildpayone'), // Online bank transfer
        'wlt' => get_string('wlt', 'enrol_ildpayone') // e-Wallet
    );
    $settings->add(new admin_setting_configmultiselect('enrol_ildpayone/clearing_types', get_string('clearing_types', 'enrol_ildpayone'), get_string('clearing_types_help', 'enrol_ildpayone'), array('elv', 'cc', 'vor', 'rec', 'sb', 'wlt'), $clearing_types_options));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_ildpayone/expiredaction', get_string('expiredaction', 'enrol_ildpayone'), get_string('expiredaction_help', 'enrol_ildpayone'), ENROL_EXT_REMOVED_UNENROL, $options));
}
