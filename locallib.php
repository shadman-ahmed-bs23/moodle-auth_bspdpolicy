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
 * Local lib functions for 'auth_bspdpolicy'.
 *
 * @package    auth_bspdpolicy
 * @copyright  2021 Brain Station 23 ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

defined('MOODLE_INTERNAL') || die();
define('PREF_FIELD_AUTH_BSPDPOLICY_DATE', 'auth_bspdpolicy_date');


/**
 * Password expiration check
 * Check if password needs to expire and if so
 * expired it and redirect to defined page (default new password page)
 *
 * @param object $user user object, later used for $USER
 * @param string $username (with system magic quotes)
 * @param string $password plain text password (with system magic quotes)
 *
 */
function auth_bspdpolicy_check_pwd_expiration(&$user, $username = null, $password = null) {
    $expirationdays = get_config('auth_bspdpolicy','expirationdays');
    $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
    // default date to -1 so if not found always before today
    $passwordexpdate = get_user_preferences(PREF_FIELD_AUTH_BSPDPOLICY_DATE, -1, $user->id);
    // If not settings found don't expire otherwise check date
    $passwordexpired = (($expirationdays != null && $expirationdays !== false) && ($passwordexpdate <= $today));
    if ($passwordexpired) {
        // force new password
        set_user_preference('auth_forcepasswordchange', 1, $user->id);

        // set new date for password expiration.
        $newexpdate = mktime(0, 0, 0, date("m")  , (date("d") + $expirationdays), date("Y"));
        set_user_preference(PREF_FIELD_AUTH_BSPDPOLICY_DATE, $newexpdate, $user->id);
    }
}

function auth_bspdpolicy_generate_token($username) {
    global $DB;
    $otpcode = rand(100000, 999999);
    $userstat = $DB->get_record_select('auth_bspdpolicy', "username = :username", array('username' => $username));
    if (!$userstat) {
        auth_bspdpolicy_create_user_instance($username, $otpcode);
    } else {
        $userstat->otp = $otpcode;
        $userstat->status = 1;
        $userstat->timevalid = auth_bspdpolicy_otp_validity();
        $DB->update_record('auth_bspdpolicy', $userstat);
    }

    return $otpcode;
}

function auth_bspdpolicy_send_otp_mail($userinfo) {
    global $CFG, $DB; // adding otp mail code
    $tokenvalidtime = get_config('auth_bspdpolicy', 'tokenvalidity');
    $emailtemplatetext = get_config('auth_bspdpolicy', 'emailtemplate');

    // Check email logs for rate limiting. 
    // User will be able to receive five emails without using the token.

    $sql = "SELECT *
            FROM {auth_bspdpolicy_email_logs}
            WHERE username = :username AND timesent > :lasthour AND used = 0";
    $params = [
        'username'=>$userinfo->username, 
        'lasthour'=> time() - 3600,
    ];

    $emaillogs = $DB->get_records_sql($sql, $params);
    if(count($emaillogs) >= 5) {
        redirect(new moodle_url('/login/index.php'), get_string('emaillimitingmsg', 'auth_bspdpolicy'), 42, \core\output\notification::NOTIFY_ERROR);
    }

    $useremail = new stdClass();
    $useremail->email = $userinfo->email; // for testing example 'testuser@yopmail.com';
    $useremail->id = $userinfo->id;
    $subject = get_string('emailsubject', 'auth_bspdpolicy');
    $otp = auth_bspdpolicy_generate_token($userinfo->username);

    $emailwithtoken = str_replace('{{OTP}}', $otp, $emailtemplatetext);
    // Data to be passed in the email template.
    $data = new stdClass();
    $data->username = $userinfo->username;
    $data->otp = $otp;
    $data->tokenvalidtime = $tokenvalidtime;
    // $messagetxt = get_string('tokenemail', 'auth_bspdpolicy', $data);
    $messagetxt = $emailwithtoken;
    $sender = new stdClass();
    $sender->email = 'tester@example.com';
    $sender->id = -98;
    // Manage Moodle debugging options.
    $debuglevel = $CFG->debug;
    $debugdisplay = $CFG->debugdisplay;
    $debugsmtp = $CFG->debugsmtp ?? null; // This might not be set as it's optional.
    $CFG->debugdisplay = true;
    $CFG->debugsmtp = true;
    $CFG->debug = 15;
    // send email
    ob_start();
    $success = email_to_user($useremail, $sender, $subject, $messagetxt, '', '', '', false);

    $smtplog = ob_get_contents();
    ob_end_clean();

    // Save email log after sending email.
    $emaillog = new stdClass();
    $emaillog->username = $userinfo->username;
    $emaillog->email = $userinfo->email;
    $emaillog->otp = $otp;
    $emaillog->used = 0;
    $emaillog->timesent = time();
    $DB->insert_record("auth_bspdpolicy_email_logs", $emaillog);
    return $smtplog;

}

function auth_bspdpolicy_create_user_instance($username, $otpcode) {
    global $DB;
    $userstat = new stdClass();
    $userstat->username = $username;
    $userstat->status = 1;
    $userstat->timevalid = auth_bspdpolicy_otp_validity();
    $userstat->otp = $otpcode;
    $DB->insert_record("auth_bspdpolicy", $userstat);
}
/**
 * Checks if the user is an admin/internal user.
 *
 * @param object $user user object, later used for $USER
 */
function auth_bspdpolicy_is_admin_user($user): bool {
    $internalemailslist = get_config('auth_bspdpolicy', 'internal_emails');
    $emails = explode(",", $internalemailslist);
    foreach($emails as $email) {
        $mail = $user->email;
        if (strpos($mail, trim($email)) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Returns a timestamp after calculating token expiration time.
 *
 * @return string $otpvalidity
 */
function auth_bspdpolicy_otp_validity() {
    $config = get_config('auth_bspdpolicy', 'tokenvalidity');
    $tokenvalidity = (int)$config;
    if ($tokenvalidity == null || $tokenvalidity == '') {
        $tokenvalidity = 2;
    }
    $otpvalidity = time() + ($tokenvalidity * 60);
    return $otpvalidity;
}
