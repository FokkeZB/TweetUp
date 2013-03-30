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

// 4. Set your group's path (e.g. 'my-group' for meetup.com/my-group) here:
$config['group_urlname'] = '';

// Change the tweet templates or set to FALSE if you don't the category to be published
$config['tweet_event'] = "New event at :date: ':name' :url";
$config['tweet_discussion'] = "New discussion: ':subject' :url";

// Change the date format and set your locale
$config['format'] = '%e %B';
// setlocale(LC_ALL, 'nl_NL') or exit("Unsupported locale set in 'config.php'.");

// TweetUp will create a file called 'tweetup.since' containing a timestamp so that only
// newer activity will be published. To prevent TweetUp from publishing all activity the
// first time or after deleting the file, it will fall back to the inode change time
// of this 'config.php' file. You can also set your own, like;
// 
// $config['since'] = mktime(0,0,0,12,31,2010);

$config['since'] = filectime(__FILE__);