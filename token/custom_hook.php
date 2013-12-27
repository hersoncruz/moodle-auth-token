<?php
/*
TOKEN Authentication Plugin Custom Hook


This file acts as a hook for the TOKEN Authentication plugin. The plugin will
call the functions defined in this file in certain points in the plugin
lifecycle.

Use this sample file as a template. You should copy it and not modify it
in place since you may lost your changes in future updates.

To use this hook you have to go to the config form in the admin interface of
Moodle and set the full path to this file. Please note that the default value
for such a field is this custom_hook.php file itself.

You should not change the name of the funcions since that's the API the plugin
expect to exist and to use.

Read the comments of each function to discover when they are called and what
are they for.
*/


/*
 name: token_hook_attribute_filter
 arguments:
   - $token_attributes: array of TOKEN attributes
 return value:
   - nothing
 purpose: this function allows you to modify the array of TOKEN attributes.
          You can change the values of them (e.g. removing the non desired
          urn parts) or you can even remove or add attributes on the fly.
*/
function token_hook_attribute_filter(&$token_attributes) {

/*
    // Nos quedamos sólamente con el DNI dentro del schacPersonalUniqueID
    if(isset($token_attributes['schacPersonalUniqueID'])) {
        foreach($token_attributes['schacPersonalUniqueID'] as $key => $value) {
            $data = array();
            if(preg_match('/urn:mace:terena.org:schac:personalUniqueID:es:(.*):(.*)/', $value, $data)) {
                $token_attributes['schacPersonalUniqueID'][$key] = $data[2];
                //DNI sin letra
                //$token_attributes['schacPersonalUniqueID'][$key] = substr($value[2], 0, 8);
            }
            else {
                unset($token_attributes['schacPersonalUniqueID'][$key]);
            }
        }
    }

    // Pasamos el irisMailMainAddress como mail si no existe
    if(!isset($token_attributes['mail'])) {
        if(!isset($token_attributes['irisMailMainAddress'])) {
            $token_attributes['mail'] = $token_attributes['irisMailMainAddress'];
        }
    }


    // Pasamos el uid como eduPersonPrincipalName o como eduPersonTargetedID
    if(!isset($token_attributes['eduPersonPrincipalName'])) {
        if(!isset($token_attributes['uid'])) {
            $token_attributes['eduPersonPrincipalName'] = $token_attributes['uid'];
        }
        else if (isset($token_attributes['eduPersonTargetedID'])) {
            $token_attributes['eduPersonPrincipalName'] = $token_attributes['eduPersonTargetedID'];
        }
        else if (isset($token_attributes['mail'])) {
            $token_attributes['eduPersonPrincipalName'] = $token_attributes['mail'];
        }
    }


    // Pasamos el uid como eduPersonPrincipalName

    if(!isset($token_attributes['eduPersonPrincipalName'])) {
        if(!isset($token_attributes['uid'])) {
            $token_attributes['eduPersonPrincipalName'] = $token_attributes['uid'];
        }
        else if (isset($token_attributes['mail'])) {
            $token_attributes['eduPersonPrincipalName'] = $token_attributes['mail'];
        }
    }
*/

}

/*
 name: token_hook_user_exists
 arguments:
   - $username: candidate name of the current user
   - $token_attributes: array of TOKEN attributes
   - $user_exists: true if the $username exists in Moodle database
 return value:
   - true if you consider that this username should exist, false otherwise.
 purpose: this function let you change the logic by which the plugin thinks
          the user exists in Moodle. You can even change the username if
          the user exists but you want to recreate with another name.
*/
function token_hook_user_exists(&$username, $token_attributes, $user_exists) {
    return true;
}

/*
 name: token_hook_authorize_user
 arguments:
    - $username: name of the current user
    - $token_attributes: array of TOKEN attributes
    - $authorize_user: true if the plugin thinks this user should be allowed
 return value:
    - true if the user should be authorized or an error string explaining
      why the user access should be denied.
 purpose: use this function to deny the access to the current user based on
          the value of its attributes or any other reason you want. It is
	  very important that this function return either true or an error
	  message.
*/
function token_hook_authorize_user($username, $token_attributes, $authorize_user) {
    return true;
}

/*
 name: token_hook_post_user_created
 arguments:
   - $user: object containing the Moodle user
 return value:
   - nothing
 purpose: use this function if you want to make changes to the user object
          or update any external system for statistics or something similar.
*/
function token_hook_post_user_created($user) {

}
