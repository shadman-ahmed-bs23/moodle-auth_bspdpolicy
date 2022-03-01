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

$username = optional_param('username', 'null', PARAM_TEXT);
$token = optional_param('token', 'null', PARAM_TEXT);

if ($token == 'null') {
    $userinfo = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
    auth_bspdpolicy_send_otp_mail($userinfo);

    $templetecontext = (object)[
        'username' => $username
    ];
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('auth_bspdpolicy/input_token', $templetecontext);
    echo $OUTPUT->footer();
} else {
    $userstat = $DB->get_record_select('auth_bspdpolicy', "username = :username", array('username' => $username));var_dump($userstat);
    //die;
    $currentime = time();

    if ($userstat->otp == $token && $userstat->timevalid >= $currentime && (int)$userstat->status == 1) {
        $userstat->status = 0;
        $DB->update_record('auth_bspdpolicy', $userstat);
        $user =  $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
        // for password expiration check
        auth_bspdpolicy_check_pwd_expiration($user);
        // user login
        complete_user_login($user);
        redirect($CFG->wwwroot, "login successful", null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect($CFG->wwwroot . "/login/index.php", get_string('invalidtoken', 'auth_bspdpolicy'), null, \core\output\notification::NOTIFY_ERROR);
    }
}
