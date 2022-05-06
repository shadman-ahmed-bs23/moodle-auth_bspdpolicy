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
 * Language file
 *
 * @package    auth
 * @subpackage bspdpolicy
 * @author     2022 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_bspdpolicytitle'] = 'BS Password Policy';
$string['auth_bspdpolicydescription'] = "This authenticator checks if the internal user's password needs to expire.<br/>If so, it will set the flag to force the account to change its password and redirect to the given URL.<br/><br/>Be sure to save these settings at least once and after each change.";
$string['auth_server_settings'] = "Password expiration check settings";
$string['auth_expirationdays_key'] = "Days until expiry";
$string['auth_expirationdays'] = "Number of days after which the password needs to expire.";
$string['auth_redirecturl_key'] = "Redirect URL";
$string['auth_redirecturl'] = "URL to redirect to when password has expired.";
$string['pluginname'] = "BS Password Policy";
$string['general_heading'] = "General";
$string['internal_emails'] = "List of internal emails: ";
$string['internal_emails_help'] = "List of email domains in comma-separated format. Examples: @google.com, @facebook.com";
$string['expirationdays'] = "Days until expiry";
$string['expirationdays_help'] = "Number of days after which the password needs to expire";
$string['token_page_title'] = "Token Input";
$string['tokenvalidity_visiblename'] = 'Token validation time';
$string['tokenvalidity_description'] =  'Minutes after token becomes invalid';
$string['invalidtoken'] = 'Login failed, expired token was provided.';
$string['wrongtoken'] = 'Login failed, invalid token';
$string['tokenemail'] = 'Hi, {$a->username}

Use the following OTP to complete your sign in process. OTP is valid for {$a->tokenvalidtime} mintues.

<h3>{$a->otp}</h3>

Regards. ';
$string['emailsubject'] = 'Verify Moodle Sign In!';
$string['sitename'] = 'Abbvie CME Academy';
$string['loginbtntext'] = 'Log in';
$string['tokenplaceholder'] = 'Token';
$string['expiredtokenmsg'] = 'Login failed, token expired.';
//$string['expiredtokenmsg'] = 'Login failed, expired token was provided. Please {$a->loginurl} again';

// Raw text replacement in input token page for expired token message.
$string['text1'] = 'Please';
$string['text2'] = 'again';
$string['loginsuccessfultxt'] = 'Login successful!';
$string['emailtemplatefield'] = 'Email Template: ';
$string['emailtemplatefield_description'] = 'Email template for the token confirmation email. Put the placeholder for OTP inside this bracket {{}}. Example: Hi, your OTP for login: {{OTP}}.';
$string['defaulttemplate'] = 'Hi, Use the following OTP to complete your login process: {{OTP}}';
$string['emaillimitingmsg'] = 'Token emails have been sent too many times. Try again after 1 hour.';