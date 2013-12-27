<?php

// This file is developed for Moodle - http://moodle.org/ by Paradiso Solutions - http://www.paradisosolutions.com
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
 * Token auth plugin, reserves username, prevents normal login.
 * TODO: add IP restrictions and some other features - MDL-17135
 *
 * @package    auth
 * @subpackage token
 * @copyright  2013 Herson Cruz (herson@paradisosolutions.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * Token auth plugin.
 */
class auth_plugin_token extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_token() {
        $this->authtype = 'token';
        $this->config = get_config('auth/token');
        $this->roleauth = 'auth_token';
        $this->errorlogtag = '[AUTH TOKEN] ';
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
	    // if true, user_login was initiated by token/index.php
	    if(isset($GLOBALS['token_login']) && $GLOBALS['token_login']) {
	        unset($GLOBALS['token_login']);
	        return true;
	    }
      return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object  (with system magic quotes)
     * @param  string  $newpassword Plaintext password (with system magic quotes)
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }
    
    function loginpage_hook() {

	    global $CFG;

        if (empty($CFG->alternateloginurl)) {
            $CFG->alternateloginurl = $CFG->wwwroot.'/auth/token/login.php';
        }

	    // Prevent username from being shown on login page after logout
	    $CFG->nolastloggedin = true;
	    $GLOBALS['CFG']->nolastloggedin = true;
    }

    function logoutpage_hook() {
	    if(isset($this->config->dosinglelogout) && $this->config->dosinglelogout) {
	        set_moodle_cookie('nobody');
	        require_logout();
	        redirect($GLOBALS['CFG']->wwwroot.'/auth/token/index.php?logout=1');
	    }
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
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
      global $CFG, $OUTPUT;
      include 'config.html';
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {

      // TOKEN settings
      if (!isset($config->salt)) {
          $config->salt = '';
      }
      if (!isset($config->referrers)) {
          $config->referrers = '';
      }
      if (!isset ($config->tokenlogoimage) || $config->tokenlogoimage == NULL) {
          $config->tokenlogoimage = 'token.jpg';
      }
      if (!isset ($config->tokenlogoinfo)) {
          $config->tokenlogoinfo = 'Token login';
      }
      if (!isset ($config->tokenlogfile)) {
          $config->tokenlogfile = '';
      }

              // save token settings
      set_config('salt', trim($config->salt), 'auth/token');
      set_config('referrers', $config->referrers, 'auth/token');
      set_config('tokenlogoimage', $config->tokenlogoimage, 'auth/token');
      set_config('tokenlogoinfo', $config->tokenlogoinfo, 'auth/token');
      set_config('tokenlogfile', $config->tokenlogfile, 'auth/token');

      return true;
    }


   /**
     * Confirm the new user as registered. This should normally not be used,
     * but it may be necessary if the user auth_method is changed to manual
     * before the user is confirmed.
     */
    function user_confirm($username, $confirmsecret = null) {
        return AUTH_CONFIRM_ERROR;
    }
    
   /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     */
    function user_signup($user, $notify=true) {
        global $CFG, $DB, $PAGE, $OUTPUT;

        require_once($CFG->dirroot.'/user/profile/lib.php');

        if ($this->user_exists($user->username)) {
            print_error('auth_token_user_exists', 'auth_token');
        }

        $plainslashedpassword = $user->password;
        //unset($user->password);

/*
        if (! $this->user_create($user, $plainslashedpassword)) {
            print_error('auth_token_create_error', 'auth_token');
        }
*/

        $user->id = $DB->insert_record('user', $user);

        // Save any custom profile field information
        profile_save_data($user);

//        $this->update_user_record($user->username);
        update_internal_user_password($user, $plainslashedpassword);

        $user = $DB->get_record('user', array('id'=>$user->id));
        events_trigger('user_created', $user);

        if (! send_confirmation_email($user)) {
            print_error('noemail', 'auth_token');
        }

        if ($notify) {
            $emailconfirm = get_string('emailconfirm');
            $PAGE->set_url('/auth/token/auth.php');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($emailconfirm);
            echo $OUTPUT->header();
            notice(get_string('emailconfirmsent', '', $user->email), "{$CFG->wwwroot}/index.php");
        } else {
            return true;
        }
    }

}
