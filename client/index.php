<?php
	$a = array();
	$a['oauth_token'] = null;
	$a['oauth_token_secret'] = null;
	$a['authentification_url'] = null;
	
	$oauth_client = new Oauth("key","secret");
	$oauth_client->enableDebug();
	try {
		$info = $oauth_client->getRequestToken("http://192.168.187.132/oauth/request_token?oauth_callback=http://192.168.187.132/client/callback.php");
		
		// Merge in the dummy values, to surpress missing index warnings.
		$info = array_merge($a, $info);
		
		echo "<h1>We have a request token !</h1>";
		echo "<strong>Request token</strong> : ".$info['oauth_token']."<br />";
		echo "<strong>Request token secret</strong> : ".$info['oauth_token_secret']."<br />";
		echo "to authenticate go <a href=\"".$info['authentification_url']."?oauth_token=".$info['oauth_token']."\">here</a>";
	} catch(OAuthException $E){
		echo "<pre>".print_r($E->debugInfo,true)."</pre>";
	}
?>
