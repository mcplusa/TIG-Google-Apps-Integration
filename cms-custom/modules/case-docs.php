<?php

$a = $case_row;
$a['recipient'] = $case_row['client_id'];
$a['client'] = $case_row['client_id'];

require_once('/var/www/html/cms-custom/extensions/google_drive_connector/index.php');

// I don't have permission to edit pikaDrive on dev server at the moment, this
// is a work around.
//if (pikaDrive::isAuthenticated($auth_row["username"]))
if (file_exists('/var/www/html/cms-custom/extensions/google_drive_connector/tokens' . $auth_row['username']))
{
	$pika = new PikaDrive($auth_row["username"]);
	$filez = $pika->listFiles();
	//echo json_encode($filez); exit();
	$g = '';
	
	foreach ($filez as $f)
	{
		$g .= "<a href=\"{$f['webContentLink']}\"><img src=\"{$f['iconLink']}\"> ";
		$g .= $f['title'];
		$g .= "</a><br>";
	}
	
	$a['google_drive_files'] = $g;
}

else
{
	$a['google_drive_files'] = '<a href="/api/v1/drive/auth?username=' ;
	$a['google_drive_files'] .= htmlspecialchars($auth_row['username']);
	$a['google_drive_files'] .= '">Please log into Google Drive</a>';
}

$template = new pikaTempLib('subtemplates/case-docs.html', $a);
$C .= $template->draw();
?>
