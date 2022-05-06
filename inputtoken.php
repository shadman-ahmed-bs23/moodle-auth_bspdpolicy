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
 * Admin auth input token functions
 *
 * @package    auth_bspdpolicy
 * @copyright  2021 Brain Station 23 ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . "/moodlelib.php");
require_once(__DIR__ . '/locallib.php');

global $PAGE, $OUTPUT, $DB;

$PAGE->set_url(new moodle_url('/auth/bspdpolicy/inputtoken.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('token_page_title', 'auth_bspdpolicy'));

//$username = optional_param('username', 'null', PARAM_TEXT);
$token = optional_param('token', null, PARAM_TEXT);
$wrongtoken = optional_param('wrongtoken', 0, PARAM_TEXT);
$expiredtoken = optional_param('expiredtoken', 0, PARAM_TEXT);
if (isset($SESSION->username)) {
    $username = $SESSION->username;
}

if (!$token) {
    $userinfo = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
    if (!$wrongtoken && !$expiredtoken) {
        auth_bspdpolicy_send_otp_mail($userinfo);
    }
    // Login url to be passed in expired token message.
    $data = new stdClass();
    //$data->loginurl = new moodle_url('/login/index.php');
    $data->loginurl = $CFG->wwwroot . "/login/index.php";
    $templetecontext = (object)[
        'username' => $username,
        'iswrong' => $wrongtoken,
        'isexpired' => $expiredtoken,
        'sitename' => get_string('sitename', 'auth_bspdpolicy'),
        'tokenplaceholder' => get_string('tokenplaceholder', 'auth_bspdpolicy'),
        'loginbtntext' => get_string('loginbtntext', 'auth_bspdpolicy'),
        'warningmessage' => get_string('wrongtoken', 'auth_bspdpolicy'),
        'expiredtokenmsg' => get_string('expiredtokenmsg', 'auth_bspdpolicy', $data),
        'loginurl' => $data->loginurl,
        'text1' => get_string('text1', 'auth_bspdpolicy'),
        'text2' => get_string('text2', 'auth_bspdpolicy')
    ];
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('auth_bspdpolicy/input_token', $templetecontext);
    echo $OUTPUT->footer();
} else {
    $userstat = $DB->get_record_select('auth_bspdpolicy', "username = :username", array('username' => $username));
    $currentime = time();
    if ($userstat->otp == $token && $userstat->timevalid >= $currentime && (int)$userstat->status == 1) {
        $userstat->status = 0;
        $DB->update_record('auth_bspdpolicy', $userstat);
        
        // Update email log after each login.
        $emaillog = $DB->get_record('auth_bspdpolicy_email_logs', array('otp' => $userstat->otp));
        $emaillog->used = 1;
        $DB->update_record('auth_bspdpolicy_email_logs', $emaillog);

        $user =  $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
        // For password expiration check.
        auth_bspdpolicy_check_pwd_expiration($user);
        // User login.
        complete_user_login($user);
        redirect($CFG->wwwroot, get_string('loginsuccessfultxt', 'auth_bspdpolicy'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else if ($userstat->otp != $token && $userstat->timevalid >= $currentime && (int)$userstat->status == 1) {
        redirect(new moodle_url('/auth/bspdpolicy/inputtoken.php?wrongtoken=1'));
    } else {
        redirect(new moodle_url('/auth/bspdpolicy/inputtoken.php?expiredtoken=1'));
    }
}
