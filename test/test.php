<?php
$req_url = 'http://192.168.187.132/oauth/request_token';
$authurl = 'http://192.168.187.132/oauth/login.php';
$acc_url = 'http://192.168.187.132/oauth/access_token';
$api_url = 'http://192.168.187.132/api/v1';
$conskey = 'key';
$conssec = 'secret';

session_start();

if (!isset($_SESSION['state']))
{
	$_SESSION['state'] = 0;
}

// In state=1 the next request should include an oauth_token.
// If it doesn't go back to 0
if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
try {
  $oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();
	  if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
		  
    $request_token_info = $oauth->getRequestToken($req_url, 'http://192.168.187.132/test/callback.php');
    $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
    $_SESSION['state'] = 1;
    //echo 'Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']; exit();
    header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
    exit;
    
  } else if($_SESSION['state']==1) {
    
	   $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
    $access_token_info = $oauth->getAccessToken($acc_url);
    $_SESSION['state'] = 2;
    $_SESSION['token'] = $access_token_info['oauth_token'];
    $_SESSION['secret'] = $access_token_info['oauth_token_secret'];
    
  } 
  
  $oauth->setToken($_SESSION['token'],$_SESSION['secret']);
  $oauth->fetch("$api_url/user.json");
  $json = json_decode($oauth->getLastResponse());
  print_r($json);
  
} catch(OAuthException $E) {
  print_r($E);
}
?>