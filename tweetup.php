<?php

require_once 'config.php';
require_once 'vendors/codebird/src/codebird.php';
require_once 'vendors/meetup/meetup.php';

header('Content-Type: text/plain; charset=UTF-8');

if ($missing = array_diff(array('consumer_key', 'consumer_secret', 'oauth_token', 'oauth_token_secret', 'key', 'group_urlname', 'format', 'last'), array_keys($config))) {
	exit("Update config.php with '" . implode("', '", $missing) . "'.");
}

try {

	Codebird::setConsumerKey($config['consumer_key'], $config['consumer_secret']);
	$cb = Codebird::getInstance();
	$cb->setToken($config['oauth_token'], $config['oauth_token_secret']);

	$Meetup = new Meetup(array(
		'key' => $config['key'],
	));
	
	function fromEDT($edt) {
		$parsed = strptime(str_replace('EDT ', '-0400', $edt), '%a %b %d %H:%M:%S %z %Y');
		return mktime($parsed['tm_hour'], $parsed['tm_min'], $parsed['tm_sec'], $parsed['tm_mon'] + 1, $parsed['tm_mday'], $parsed['tm_year'] + 1900);
	}

	$last = @file_get_contents('tweetup.last');
	$last = $last ? unserialize($last) : $config['last'];

	$tweets = array();

	// Bewaar meest recente activiteit VORIGE run
	$lastActivity = $last['activity'];

	if (true) {
		$activities = $Meetup->getActivity();

		echo 'Activities (all groups) found: ' . count($activities) . PHP_EOL;

		foreach ($activities as $activity) {

			// Niet onze groep
			if ($activity->group_id != $config['group_id']) {
				continue;
			}

			$activity->published = fromEDT($activity->published);

			// Bewaren als meest recente datum
			if ($activity->published > $last['activity']) {
				$last['activity'] = $activity->published;
			}

			// Dit is eerste run, dan tweet we niets
			if (!$lastActivity) {
				break;
			}

			// Datum is gelijk of eerder dan meest recente van VORIGE run, dan stoppen we
			if ($activity->published <= $lastActivity) {
				break;
			}
				
			echo 'New activity: ' . $activity->title . ' (' . $activity->link . ')' . PHP_EOL;

			switch ($activity->item_type) {
				case 'new_rsvp': 
					$msg = $activity->member_name . ' RSVP\'d for ' . $activity->event_name;
					break;
				case 'new_member': 
					$msg = 'Welcoming ' . $activity->member_name . ($activity->bio ? ', ' . $activity->bio : '') . ' as our newest member!';
					break;
				case 'new_discussion': 
					$msg = 'Join ' . $activity->member_name . '\'s discussion about "' . $activity->discussion_title . '"';
					break;
				case 'new_reply': 
					$msg = $activity->member_name . ' posted a reply to ' . $activity->discussion_title;
					break;
				case 'new_checkin': 
					$msg = $activity->member_name . ' checked in to ' . $activity->event_name;
					break;
				case 'photo_upload': 
					$msg = $activity->member_name . ' uploaded a picture of ' . $activity->album_name;
					break;
				case 'photo_upload_multi': 
					$msg = $activity->member_name . ' uploaded new pictures of ' . $activity->album_name;
					break;
				default:
					$msg = false;
			}

			if ($msg) {
				$tweet = $msg . ' ' . $activity->link . ' #tidevs';
				
				$reply = $cb->statuses_update(array(
					'status' => $tweet,
				));
				
				echo 'TWEET: ' . $tweet . PHP_EOL;
				
				if (isset($reply->httpstatus) && $reply->httpstatus == 200) {
					echo 'POSTED: https://twitter.com/' . $reply->user->screen_name . '/status/' . $reply->id_str . PHP_EOL;
				
				} elseif (isset($reply->errors[0]->message)) {
					echo 'ERROR: ' . $reply->errors[0]->message . PHP_EOL;
					
				} else {
					echo 'ERROR: Unknown error posting tweet.' . PHP_EOL;
				}

			} else {
				echo 'ERROR: Unsupported activity type: ' . $activity->item_type . PHP_EOL;
			}
		}
	}
	
	if ($config['tweet_event']) {
		$events = $Meetup->getEvents(array(
			'group_urlname' => $config['group_urlname'],
		));

		echo 'Upcomming events found: ' . count($events) . PHP_EOL;

		foreach ($events as $event) {
	
			if (!$last['event'] || $event->id <= $last['event']) {

				if (!$last['event']) {
					$last['event'] = $event->id;
				}

				break;
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
				echo 'ERROR: Unknown error posting tweet.' . PHP_EOL;
			}
		}
	}

	if (@file_put_contents('tweetup.last', serialize($last)) === false) {
		echo "Cannot write to 'tweetup.last'.";
	}

} catch (Exception $e) {
	
	if ($last && @file_put_contents('tweetup.last', serialize($last)) === false) {
		echo "Cannot write to 'tweetup.last'.";
	}

	exit('Exception: ' . $e->getMessage());
}