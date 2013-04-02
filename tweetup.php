<?php

require_once 'config.php';
require_once 'vendors/codebird/src/codebird.php';
require_once 'vendors/meetup/meetup.php';

header('Content-Type: text/plain; charset=UTF-8');

if ($missing = array_diff(array('consumer_key', 'consumer_secret', 'oauth_token', 'oauth_token_secret', 'key', 'group_urlname', 'format', 'since'), array_keys(array_filter((array) $config, 'strlen')))) {
	exit("Update config.php with '" . implode("', '", $missing) . "'.");
}

try {

	Codebird::setConsumerKey($config['consumer_key'], $config['consumer_secret']);
	$cb = Codebird::getInstance();
	$cb->setToken($config['oauth_token'], $config['oauth_token_secret']);

	$Meetup = new Meetup(array(
		'key' => $config['key'],
	));
	
	function milliseconds($time = null) {
		return (int) round(($time ? (int) $time : microtime(true)) * 1000);
	}

	$since = (int) @file_get_contents('tweetup.since');

	if ($since == 0) {
		$since = milliseconds($config['since']);
	}

	if (@file_put_contents('tweetup.since', milliseconds()) === false) {
		exit("Cannot write timestamp to 'tweetup.since'.");
	}
	
	echo 'Processing activity since ' . date('Y-m-d H:i:s', $since / 1000) . ' (' . $since . ')' . PHP_EOL;

	$tweets = array();
	
	if ($config['tweet_event']) {
		$events = $Meetup->getEvents(array(
			'group_urlname' => $config['group_urlname'],
		));

		echo 'Upcomming events found: ' . count($events) . PHP_EOL;

		foreach ($events as $event) {
	
			if ($event->created < $since) {
				continue;
			}
				
			echo 'New event: ' . $event->name . ' (' . $event->event_url . ')' . PHP_EOL;
		
			$tweet = strtr($config['tweet_event'], array(
				':name'	=> $event->name,
				':date'	=> trim(strftime($config['format'], $event->time / 1000)),
				':url'	=> $event->event_url,
			));
			
			$reply = $cb->statuses_update(array(
				'status' => $tweet,
			));
			
			echo 'TWEET: ' . $tweet . PHP_EOL;
			
			if (isset($reply->httpstatus) && $reply->httpstatus == 200) {
				echo 'POSTED: https://twitter.com/' . $reply->user->screen_name . '/status/' . $reply->id_str . PHP_EOL;
			
			} elseif (isset($reply->errors[0]->message)) {
				echo 'ERROR: ' . $reply->errors[0]->message . PHP_EOL;
				
			} else {
				echo 'ERROR: Unknown error posting tweet.';
			}
		}
	}
	
	if ($config['tweet_discussion']) {
	
		$boards = $Meetup->getDiscussionBoards(array(
			'urlname' => $config['group_urlname'],
		));
	
		echo 'Discussion boards found: ' . count($boards) . PHP_EOL;

		foreach ($boards as $board) {
			echo 'Board: ' . $board->name . PHP_EOL;
				
			$discussions = $Meetup->getDiscussions(array(
				'urlname' => $config['group_urlname'],
				'bid' => $board->id,
			));
			
			echo 'Discussions found: ' . count($discussions) . PHP_EOL;
			
			foreach ($discussions as $discussion) {
	
				if ($discussion->created < $since) {
					continue;
				}
				
				$url = 'http://www.meetup.com/' . $config['group_urlname'] . '/messages/boards/thread/' . $discussion->id;
		
				echo 'New discussion: ' . $discussion->subject . ' (' . $url . ')' . PHP_EOL;
		
				$tweet = strtr($config['tweet_discussion'], array(
					':subject'	=> $discussion->subject,
					':date'	=> trim(strftime($config['format'], $discussion->created / 1000)),
					':url'	=> $url,
				));
			
				$reply = $cb->statuses_update(array(
					'status' => $tweet,
				));
			
				echo 'TWEET: ' . $tweet . PHP_EOL;
			
				if (isset($reply->httpstatus) && $reply->httpstatus == 200) {
					echo 'POSTED: https://twitter.com/' . $reply->user->screen_name . '/status/' . $reply->id_str . PHP_EOL;
			
				} elseif (isset($reply->errors[0]->message)) {
					echo 'ERROR: ' . $reply->errors[0]->message . PHP_EOL;
				
				} else {
					echo 'ERROR: Unknown error posting tweet.';
				}
			}
		}
	}

} catch (Exception $e) {
	exit('Exception: ' . $e->getMessage());
}