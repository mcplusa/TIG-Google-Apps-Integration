<?php

require_once('/var/www/html/cms-custom/extensions/google_drive_connector/index.php');

if (strlen($case1->google_drive_folder_id) == 0)
{
	$drive = new PikaDrive($auth_row['username']);
	$x = $drive->createFolder($case1->case_id);
	$case1->google_drive_folder_id = $x['id'];
	$case1->save();
}

$a = $case_row;
$a['recipient'] = $case_row['client_id'];
$a['client'] = $case_row['client_id'];

$template = new pikaTempLib('subtemplates/case-docs.html', $a);
$C .= $template->draw();
?>
