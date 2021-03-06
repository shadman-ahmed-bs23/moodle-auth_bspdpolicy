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
 * Settings for auth_bspdpolicy
 *
 * @package    auth
 * @subpackage bspdpolicy
 * @author     2022 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// This is used for performance, we don't need to know about these settings on every page in Moodle, only when
// we are looking at the admin settings pages.
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('internal_emails', get_string('general_heading', 'auth_bspdpolicy'),''));

    $settings->add(new admin_setting_configtextarea('auth_bspdpolicy/internal_emails',
        get_string('internal_emails', 'auth_bspdpolicy'),
        get_string('internal_emails_help', 'auth_bspdpolicy'), ''));

    $settings->add(new admin_setting_configtext('auth_bspdpolicy/expirationdays',
        get_string('expirationdays', 'auth_bspdpolicy'),
        get_string('expirationdays_help', 'auth_bspdpolicy'), ''));

    $settings->add(new admin_setting_configtext(
        'auth_bspdpolicy/tokenvalidity',
        get_string('tokenvalidity_visiblename', 'auth_bspdpolicy'),
        get_string('tokenvalidity_description', 'auth_bspdpolicy'),
        2
    ));

    $settings->add(new admin_setting_configtextarea(
        'auth_bspdpolicy/emailtemplate',
        get_string('emailtemplatefield', 'auth_bspdpolicy'),
        get_string('emailtemplatefield_description', 'auth_bspdpolicy'),
        get_string('defaulttemplate', 'auth_bspdpolicy')
    ));
}
