<?php

require_once('/var/www/html/cms-custom/extensions/google_drive_connector/index.php');

if (strlen($a['google_drive_folder_id']) == 0)
{
	$drive = new PikaDrive($auth_row['username']);
	$x = $drive->createFolder($case1->case_id);
	$case1->google_drive_folder_id = $x['id'];
	$case1->save();
}

$a = $case_row;
$a['recipient'] = $case_row['client_id'];
$a['client'] = $case_row['client_id'];


/*
	require_once('/var/www/html/cms-custom/extensions/google_drive_connector/index.php');

// I don't have permission to edit pikaDrive on dev server at the moment, this
// is a work around.
//if (pikaDrive::isAuthenticated($auth_row["username"]))
if (file_exists('/var/www/html/cms-custom/extensions/google_drive_connector/tokens/' . $auth_row['username']))
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
	
	// Add a "Log out of Google Drive" button at the bottom
	$a['google_drive_files'] .= '<a class="btn btn-mini" href="/api/v1/drive/unauthorize">' ;
	$a['google_drive_files'] .= 'Log out of Google Drive</a>';
}

else
{
	$a['google_drive_files'] = '<a class="btn btn-large" href="/api/v1/drive/auth?username=' ;
	$a['google_drive_files'] .= htmlspecialchars($auth_row['username']);
	$a['google_drive_files'] .= '">Please log into Google Drive</a>';
}
*/

$template = new pikaTempLib('subtemplates/case-docs.html', $a);
$C .= $template->draw();
?>
