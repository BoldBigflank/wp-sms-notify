<?php
/**
 * @package SMS_Notify
 * @version 1.0
 */
/*
Plugin Name: SMS Notify
Description: This has two functions. First, it adds a 'mobile' field to users' profiles, along with a 'notifications' check box. Second, it adds a hook to send an SMS to each user when a post is published.
Author: Alex Swan
Version: 1.0
Author URI: http://bold-it.com/
*/

// Require the bundled autoload file - the path may need to change
// based on where you downloaded and unzipped the SDK
require __DIR__ . '/twilio-php-master/Twilio/autoload.php';

// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;


// This will add the Phone field to the user's profile page
// Now to get a user's phone number we use
// $mobilePhone = get_user_meta('mobile');
function modify_contact_methods ($profile_fields) {
    $profile_fields['mobile'] = "Mobile Phone (12223334444)";

    return $profile_fields;
}
add_filter('user_contactmethods','modify_contact_methods');

// This will be the hook we'll add to the publish event.
// $userPhones = get_users( '' )
function post_published_notification ( $ID, $post ) {
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
    $token = 'auth_token_here';
    // A Twilio phone number you purchased at twilio.com/console
    $from = "+15555550199";
    $client = new Client($sid, $token);

    $title = $post->post_title;
    $permalink = get_permalink( $ID );
    $body = sprintf('New Post: %s %s', $title, $permalink);

    $blogusers = get_users( 'blogid=$ID&role=subscriber' );
    foreach ( $blogusers as $user ) {
        $to = get_user_meta($user->ID, 'mobile', true);
        if ( intval($to) == 0 ) { continue; }
        // Use the client to do fun stuff like send text messages!
        $client->messages->create(
            // the number you'd like to send the message to
            $to,
            array(
                'from' => $from,
                // the body of the text message you'd like to send
                'body' => $body
            )
        );
    }
}
add_action('publish_post', 'post_published_notification', 10, 2);



?>