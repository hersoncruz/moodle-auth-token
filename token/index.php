<?php

define('TOKEN_INTERNAL', 1);

require_once('../../config.php');
require_once('error.php');
require_once('../../cohort/lib.php');

global $CFG, $USER, $SESSION, $err, $DB, $PAGE;

$username = required_param('user', PARAM_TEXT);
$token = required_param('token', PARAM_TEXT );
$timestamp = required_param ('timestamp', PARAM_INT);
$email = required_param('email', PARAM_TEXT);
$cohortname = optional_param('cohortname','', PARAM_TEXT);
$newuser = optional_param('newuser',false, PARAM_BOOL);
$fn = required_param('fn', PARAM_TEXT);
$ln = required_param('ln', PARAM_TEXT);
$city = required_param('city', PARAM_TEXT);
$country = required_param('country', PARAM_TEXT);

$PAGE->set_url('/auth/token/index.php');
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));

if(isset($_GET['user']) && isset($_GET['token']) && isset($_GET['timestamp']) && isset($_GET['email'])) {
  $pluginconfig = get_config('auth/token');
  $secret_salt = $pluginconfig->salt;  
  $localtoken = crypt($timestamp.$username.$email,$secret_salt);
  
  
  if($localtoken == $token) {
    
    $current_time = time();
    
    if($current_time <= $timestamp + (30 * 60)) {
      
      $localuser = $DB->get_record('user', array('username'=> $username, 'email' => $email, 'auth' => 'token'));
      
      if (!empty($localuser)) {
        do_login();
      } else {
        // Now we are going to create the user if doesn't exists
        //$err['login'] = get_string("auth_token_error_complete_user_data", "auth_token", $username.' not found');
        //token_error($err['login'], '?logout', $pluginconfig->tokenlogfile);
        
        $created = false;
        //$user = new stdClass();
        
        $tokenauth = get_auth_plugin('token');
        
        $USER->username = $username;
        $USER->email = $email;
        $USER->auth = 'token';
        $USER->password = 'empty';
        $USER->deleted = 0;
        $USER->confirmed = 1;
        $USER->firstname = $fn;
        $USER->lastname = $ln;
        $USER->city = $city;
        $USER->country = $country;
        
        $created = $tokenauth->user_signup($USER, false);
        
        if ($created && $newuser) {
          // Create cohort for new user
          if($cohortname != '' ) {
            $cohortid = cohort_get_by_name($cohortname);
            if($cohortid != 0) {
              cohort_add_member($cohortid, $USER->id);
              add_to_log(1, 'auth/token', 'cohort_add_member','','cohort '.$cohortid.' associated to user '.$USER->id);
            } else {
              add_to_log(1, 'auth/token', 'cohort_add_member','','cohort not found');
            }
          }
          do_login();
          
        } else {
          $err['login'] = get_string("auth_token_error_general_user_login", "auth_token"); // not created
          token_error($err['login'].'--USER NOT CREATED--', '?logout', $pluginconfig->tokenlogfile);
        }
        
      }
    } else {
      $err['login'] = get_string("auth_token_error_general_user_login", "auth_token"); // timeout
      token_error($err['login'].'--TIMEOUT--', '?logout', $pluginconfig->tokenlogfile);
    }
  }
} else {
  
  $err['login'] = get_string("auth_token_error_general_user_login", "auth_token"); // wrong token
  token_error($err['login'].'--WRONG TOKEN--', '?logout', $pluginconfig->tokenlogfile);
}

function do_login() {
  // We load all moodle config and libs
  require_once('../../config.php');
  require_once('error.php');
  
  global $CFG, $USER, $SESSION, $err, $DB, $PAGE;
  global $username, $localuser;
  
  // Valid session. Register or update user in Moodle, log him on, and redirect to Moodle front
  if (file_exists('custom_hook.php')) {
      include_once('custom_hook.php');
  }

  $PAGE->set_url('/auth/token/index.php');
  $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
  $urltogo = $CFG->wwwroot;
  
  if($CFG->wwwroot[strlen($CFG->wwwroot)-1] != '/') {
      $urltogo .= '/';
  }

  // Get the plugin config for token
  $pluginconfig = get_config('auth/token');
  
  $GLOBALS['token_login'] = true;

  // Just passes time as a password. User will never log in directly to moodle with this password anyway or so we hope?
  $user = authenticate_user_login($username, time());
  if ($user === false) {
      $err['login'] = get_string("auth_token_error_general_user_login", "auth_token"); // not authenticated
      token_error($err['login'].'--NOT AUTH--', '?logout', $pluginconfig->tokenlogfile);
  }

  // Complete the user login sequence
/*
  $user = get_complete_user_data('id', $USER->id);
  if ($user === false) {
      $err['login'] = get_string("auth_token_error_general_user_login", "auth_token"); // data not completed
      token_error($err['login'].'--DATA NOT COMPLETE--', '?logout', $pluginconfig->tokenlogfile);
  }
*/
  
  $USER = complete_user_login($user);
  if (function_exists('token_hook_post_user_created')) {
      token_hook_post_user_created($USER);
  }
  
  $USER->loggedin = true;
  $USER->site = $CFG->wwwroot;
  set_moodle_cookie($USER->username);
  
  add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=".SITEID,
                 $USER->id, 0, $USER->id);

  if(isset($err) && !empty($err)) {
      token_error($err, $urltogo, $pluginconfig->tokenlogfile);
  }
  redirect($urltogo);
}

session_write_close();
redirect($CFG->wwwroot);