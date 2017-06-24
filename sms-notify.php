<?php
/**
 * @package SMS_Notify
 * @version 1.0
 */
/*
Plugin Name: SMS Notify
Plugin URI: http://wordpress.org/plugins/sms_notify/
Description: This has two functions. First, it adds a 'phone' field to users' profiles, along with a 'notifications' check box. Second, it adds a hook to send an SMS to each user when a post is published.
Author: Alex Swan
Version: 1.0
Author URI: http://bold-it.com/
*/

// This will add the Phone field to the user's profile page
// Now to get a user's phone number we use
// $mobilePhone = get_user_meta('phone');
function modify_contact_methods ($profile_fields) {
    $profile_fields['mobile'] = "Mobile Phone";

    return $profile_fields;
}
add_filter('user_contactmethods','modify_contact_methods');

?>