<?php

$a = $case_row;
$a['recipient'] = $case_row['client_id'];
$a['client'] = $case_row['client_id'];

require_once('/var/www/html/cms-custom/extensions/google_drive_connector/index.php');

if (pikaDrive::isAuthenticated($auth_row["username"]))
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

$template = new pikaTempLib('subtemplates/case-docs.html', $a);
$C .= $template->draw();
?>
