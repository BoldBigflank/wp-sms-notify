# Send SMS on WordPress Publish
## Introduction

## Setup
### Set Up Your Development Environment
I won't go in to too many details here, but you should already have a [WordPress Installation](http://wordpress.org/download/) running.

In your WordPress directory, go to `wp-content/plugins` and make a directory, I'm going to call it `sms-notify`. Inside that directory, make a file named `sms-notify.php`. In this file we'll start with some plugin metadata. This is what WordPress reads to show in our Plugin Dashboard.

```php
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

// Our code will go here

?>
```
Next, we'll activate our new plugin. Go to your WordPress Dashboard at `/wp-admin` and click the Plugins link on the left column. Click the "Activate" link on our plugin called SMS Notify.  

![Activate our SMS Notify plugin](/images/wp-plugins-list.png "Plugins Page")

Once it's active, everything should be exactly the same. Time to start modifying WordPress!


## Let Users Store Their Phone Number
We want users to be able to sign up for SMS notifications, and we'll do that by adding a Phone field in their user profile. WordPress makes this easy with the [user_contactmethods](https://codex.wordpress.org/Plugin_API/Filter_Reference/user_contactmethods) function. Add this to `sms-notify.php`:

```php
function modify_contact_methods ($profile_fields) {
    $profile_fields['mobile'] = "Mobile Phone (12223334444)";

    return $profile_fields;
}
add_filter('user_contactmethods','modify_contact_methods');
```

When WordPress shows the `user_contactmethods` data, it will now pass it to our function before going on to display it to the user. We add the 'mobile' field to it so our user can put their phone number in it. So let's try it out!

Back at `/wp-admin`, go to the Users tab and click the Add User button.  Create a user with the role Subscriber. In an incognito window, log in to your blog with that user's credentials, then go to `/wp-admin/profile.php`.  Add a phone number for your new user, then hit Save.  

![Save a phone number](/images/wp-user-profile.png "User Profile page")

## Create a Publish Post Action

Next, we'll set up a function to happen during a publish_post [publish_post](https://codex.wordpress.org/Plugin_API/Action_Reference/publish_post) action and use [get_users](https://codex.wordpress.org/Function_Reference/get_users) to iterate over all the subscribers of the blog. This is in preparation for sending a text message to each subscriber with a number attached to their profile.

```php
function post_published_notification ( $ID, $post ) {
    // TODO: Prepare Twilio stuff here

    $title = $post->post_title;
    $body = sprintf('New Post: %s', $title);

    $blogusers = get_users( 'blogid=$ID&role=subscriber' );
    foreach ( $blogusers as $user ) {
        $to = get_user_meta($user->ID, 'mobile', true);
        if (!is_int($to)) { continue; }
        // TODO: Send a text message
    }
}
add_action('publish_post', 'post_published_notification', 10, 2);
```

For each of the blog's Subscribers, it gets the phone number stored using [get_user_meta](https://codex.wordpress.org/Function_Reference/get_user_meta). Finally, gets set up to execute when the 'publish_post' action happens.  Now, to send our SMS.

## Use Twilio to Send an SMS
Go to [Twilio's PHP Library](https://www.twilio.com/docs/libraries/php) and install the library. It's recommended to use Composer, but copying the twilio-php-master folder into our sms-notify folder works as well.  Now add references to the library in the top of sms-notify.php (after the metadata).  
```php
require __DIR__ . '/twilio-php-master/Twilio/autoload.php';
use Twilio\Rest\Client;
```

Modify our `post_published_notification` function to make a Twilio client, and send a message in the foreach loop.
```php
function post_published_notification ( $ID, $post ) {
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
    $token = 'auth_token_here';
    // A Twilio phone number you purchased at twilio.com/console
    $from = "+15555550199";
    $client = new Client($sid, $token);

    $title = $post->post_title;
    $body = sprintf('New Post: %s', $title);

    $blogusers = get_users( 'blogid=$ID&role=subscriber' );
    foreach ( $blogusers as $user ) {
        $to = get_user_meta($user->ID, 'mobile', true);
        if ( intval($to) == 0 ) { continue; }
        $client->messages->create(
            $to,
            array(
                'from' => $from,
                'body' => $body
            )
        );
    }
}
add_action('publish_post', 'post_published_notification', 10, 2);
```
Be sure to update the `$sid`, `$token`, and `$from` variables to your own Account SID, Auth Token, and a Twilio phone number you own.

## Test
Head back to your WordPress admin page at `/wp-admin` and add a new Post. Write your deepest desires, then hit Publish.  
![Alt text](/images/sms-notification.png =250x "Perfect")

## What's Next


```php
code
```
