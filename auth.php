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
    function auth_plugin_bspdpolicy() {
        $this->authtype = 'bspdpolicy';
        $this->config = get_config('auth/bspdpolicy');
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
    	global $SESSION,$USER;
        $config = get_config('auth/bspdpolicy');
        $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        // default date to -1 so if not found always before today
        $passwordExpDate = get_user_preferences(PREF_FIELD_AUTH_BSPDPOLICY_DATE, -1, $user->id);
    	// If not settings found don't expire otherwise check date
        $passwordExpired = (($config != null && $config !== false) && ($passwordExpDate <= $today));
        if ($passwordExpired && ($user->auth == 'manual')) {
        	$expirationdays = $config->expirationdays;
        	$redirecturl = $config->redirecturl; 
        	
        	// force new password
        	set_user_preference('auth_forcepasswordchange', 1, $user->id);
        	
        	// set new date
        	$newexpdate = mktime(0, 0, 0, date("m")  , (date("d") + $expirationdays), date("Y"));
        	set_user_preference(PREF_FIELD_AUTH_BSPDPOLICY_DATE, $newexpdate, $user->id);
        	
        	// redirect when done
        	$SESSION->wantsurl = $redirecturl;
        }
    }
    /**
     * Checks if the user is an admin/internal user.
     *
     * @param object $user user object, later used for $USER
     */
    function is_admin_user($user) {
        $mail = $user->email;
        if (strpos($mail, 'yopmail') !== false || strpos($mail, 'Admin') !== false) {
            return true;
        }
        $uname = $user->username;
        if (strpos($uname, 'admin') !== false || strpos($uname, 'Admin') !== false) {
            return true;
        }
        return false;
    }
    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {
    	global $CFG;
        // set to defaults if undefined
        if (!isset ($config->expirationdays)) {
            $config->expirationdays = 30;
        }
        if (!isset ($config->redirecturl)) {
            $config->redirecturl = $CFG->httpswwwroot .'/login/change_password.php';
        }

        // save settings
        set_config('expirationdays', $config->expirationdays, 'auth/bspdpolicy');
        set_config('redirecturl', $config->redirecturl, 'auth/bspdpolicy');
        
        return true;
    }
}