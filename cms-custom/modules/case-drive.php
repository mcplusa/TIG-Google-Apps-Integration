<?php

require_once('/var/www/html/cms-custom/extensions/google_drive_connector/index.php');

if (strlen($case1->google_drive_folder_id) == 0)
{
	$drive = new PikaDrive($auth_row['username']);
	$x = $drive->createFolder($case1->case_id);
	$case1->google_drive_folder_id = $x['id'];
	$case1->save();
}

$a = array('google_drive_folder_id' => $case1->google_drive_folder_id);
$template = new pikaTempLib('subtemplates/case-docs.html', $a);
$C .= $template->draw();

?>
