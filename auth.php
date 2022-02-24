<?php
/**
 *
 * @package    auth
 * @subpackage bspdpolicy
 * @copyright  2013 UP learning B.V.
 * @author     Anne Krijger & David Bezemer info@uplearning.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * Authentication Plugin: Password Expire Authentication
 * 
 * Check if user has property auth_bspdpolicy_date set.
 * If not assume the password has expired
 * If date is set, check if it is today or earlier
 *  if so, password is expired
 * If Password is expired
 *  set new auth_bspdpolicy_date to today + #days as defined (default 30 days)
 *  force password reset and redirect to defined URL (default change password page)
 *   
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

define('PREF_FIELD_AUTH_BSPDPOLICY_DATE', 'auth_bspdpolicy_date');

require_once($CFG->libdir.'/authlib.php');

/**
 * Password Expire authentication plugin.
 */
class auth_plugin_bspdpolicy extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->authtype = 'bspdpolicy';
        $this->config = get_config('auth_bspdpolicy');
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    function auth_plugin_bspdpolicy() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Returns false since username password is not checked yet.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
       return false;
    }

    /**
     * Post authentication hook.
     * This method is called from authenticate_user_login() for all enabled auth plugins.
     *
     * @param object $user user object, later used for $USER
     * @param string $username (with system magic quotes)
     * @param string $password plain text password (with system magic quotes)
     *
     * Hook is used to check if password needs to expire and if so
     * expired it and redirect to defined page (default new password page)
     * 
     */
    function user_authenticated_hook(&$user, $username, $password) {
        // Password expiration should be only for the admin/internal user.
        if($this->is_admin_user($user)) {
            $this->checkPasswordExpiration($user, $username, $password);
        }
    }
       
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
    function checkPasswordExpiration(&$user, $username, $password) {
        $expirationdays = get_config('auth_bspdpolicy','expirationdays');
        $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        // default date to -1 so if not found always before today
        $passwordexpdate = get_user_preferences(PREF_FIELD_AUTH_BSPDPOLICY_DATE, -1, $user->id);
    	// If not settings found don't expire otherwise check date
        $passwordexpired = (($expirationdays != null && $expirationdays !== false) && ($passwordexpdate <= $today));
        if ($passwordexpired && ($user->auth == 'manual')) {
        	// force new password
        	set_user_preference('auth_forcepasswordchange', 1, $user->id);
        	
        	// set new date for password expiration.
        	$newexpdate = mktime(0, 0, 0, date("m")  , (date("d") + $expirationdays), date("Y"));
        	set_user_preference(PREF_FIELD_AUTH_BSPDPOLICY_DATE, $newexpdate, $user->id);
        }
    }

    /**
     * Checks if the user is an admin/internal user.
     *
     * @param object $user user object, later used for $USER
     */
    function is_admin_user($user) {
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
}