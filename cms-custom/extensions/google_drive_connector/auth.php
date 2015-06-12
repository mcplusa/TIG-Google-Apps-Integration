<?php

require_once(getcwd() . '-custom/extensions/google_drive_connector/index.php');

$rest = new PikaDrive($auth_row['username']);

if(isset($_GET['code']))
{
	$rest->setToken($auth_row['username'], $_GET['code']);
	echo "<script>window.close();</script>";
}

else if (array_key_exists(action, $_GET))
{
	if ($_GET['action'] == 'authenticate')
	{
		$rest->authenticate();
	}
	
	else if ($_GET['action'] == 'unauthorize')
	{
		$rest->unauthorize($auth_row['username']);
		echo "<script>window.close();</script>";
	}
}

?>
