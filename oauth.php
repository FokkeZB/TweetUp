<?php

require_once 'config.php';
require_once 'vendors/codebird/src/codebird.php';

header('Content-Type: text/plain; charset=UTF-8');

if ($missing = array_diff(array('consumer_key', 'consumer_secret'), array_keys((array) array_filter((array) $config, 'strlen')))) {
	exit("Update config.php with '" . implode("', '", $missing) . "'.");
}

try {
	Codebird::setConsumerKey($config['consumer_key'], $config['consumer_secret']);

	session_start();

	if (isset($_GET['oauth_verifier'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret'])) {

		$cb = new Codebird();
		$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
		$reply = $cb->oauth_accessToken(array(
			'oauth_verifier' => $_GET['oauth_verifier']
		));
		
		unset($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
		if (isset($reply->oauth_token, $reply->oauth_token_secret)) {
	
			header('Content-Type: text/plain');
			echo '// Paste these lines in your config.php:' . PHP_EOL;
			echo '$config[\'oauth_token\'] = \''. $reply->oauth_token . '\';' . PHP_EOL;
			echo '$config[\'oauth_token_secret\'] = \''. $reply->oauth_token_secret . '\';' . PHP_EOL;
			exit;
		}
	}
	
	$cb = new Codebird();

	$reply = $cb->oauth_requestToken(array(
		'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
	));

	$cb->setToken($reply->oauth_token, $reply->oauth_token_secret);

	$_SESSION['oauth_token'] = $reply->oauth_token;
	$_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;

	$auth_url = $cb->oauth_authorize();

	header('Location: ' . $auth_url);
	exit;

} catch (Exception $e) {
	exit('Exception: ' . $e->getMessage());
}