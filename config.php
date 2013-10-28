<?php

$config = array();

// 1. Create an application at https://dev.twitter.com/apps, set its type to 'Read and
//    Write' and set the consumer key and secret here:
$config['consumer_key'] = '';
$config['consumer_secret'] = '';

// 2. Open oauth.php in your browser, authenticate and paste the resulting lines here:
$config['oauth_token'] = '';
$config['oauth_token_secret'] = '';

// 3. Get your Meetup API key at http://www.meetup.com/meetup_api/key/ and set it here:
$config['key'] = '';

// 4. Set your group's path (e.g. 'my-group' for meetup.com/my-group) and id here:
$config['group_urlname'] = '';
$config['group_id'] = 123;

// Change the tweet templates or set to FALSE if you don't the category to be published
$config['tweet_event'] = "New event on :date: ':name' :url";

// Change the date format and set your locale
$config['format'] = '%B %e';
// setlocale(LC_ALL, 'nl_NL') or exit("Unsupported locale set in 'config.php'.");

$config['last'] = array();