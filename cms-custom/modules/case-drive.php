<?php


// Drive Listing Section - TODO move this to pikaCase and run it when creating a new case.
// ...or should it???  What happens if the intake person doesn't have Drive set
// up?


// Config variables
$root_folder_id = '0B-PDdyt2Y6c-fnRNRndBYWZMQ2ZRbHlJc3R0UVpoc3VqaThSOHpMb0lmUmtwZ0NQQXZxMnc';

require_once('/var/www/html/cms-custom/extensions/google_drive_connector/index.php');

function folder_search_or_create($folder_name, $parent_id)
{
	$auth_row = pikaAuth::getInstance()->getAuthRow();
	$drive = new PikaDrive($auth_row['username']);
	$sub_folders = $drive->listFiles($parent_id);
	
	foreach ($sub_folders as $sub_folder)
	{
		if ($sub_folder['title'] == $folder_name && 
		$sub_folder['mimeType'] == 'application/vnd.google-apps.folder')
		{
			return $sub_folder['id'];
		}
	}
	
	// If the foreach() loop didn't return a value, that means the folder doesn't
	// exist and it needs to be created.
	$z = $drive->createFolder($folder_name, $parent_id);
	return $z['id'];
}

if (strlen($case1->google_drive_folder_id) == 0 && !file_exists("/var/www/html/cms-custom/extensions/google_drive_connector/tokens/{$auth_row['username']}"))
{
	// Bail out to prevent errors when folder_search_or_create is run.
	$clean_username = htmlspecialchars($auth_row['username']);
	
	$C .= '<p>This case does not have a Google Drive folder.  Please log your Pika CMS account into <a class="btn btn-mini"';
	$C .= ' onClick=\'window.open("/api/v1/drive/auth?username='.$clean_username.'", "Request for Authorization", "width=600, height=400, scrollbars=yes");\'';
	$C .= '>Google Drive</a> and Pika CMS will create one.';
	$C .= '  Please reload this page once you\'ve logged in.</p>';
	
}

else
{



if (strlen($case1->google_drive_folder_id) == 0)
{
	// Reminder: It's not possible to search Google Drive if the current Pika CMS user
	// account doesn't have a token.

	// Examples:  '98765' becomes '765', '12' becomes '012'.
	$case_sub_folder_name = str_pad(substr($case1->case_id, -3), 3, '0', STR_PAD_LEFT);	
	// Look for the sub_folder where this case's folder will go.  If it doesn't
	// exist, create it.
	$case_sub_folder_id = folder_search_or_create($case_sub_folder_name, $root_folder_id);
	// Now that the sub_folder_id has either been found or created, we can find
	// (if the folder has been orphaned somehow) or create the case's folder 
	// inside the case_sub_folder (which is inside the root folder).
	$case1->google_drive_folder_id = folder_search_or_create($case1->case_id, $case_sub_folder_id);
	$case1->save();
}

$a = array('google_drive_folder_id' => $case1->google_drive_folder_id);
// End Drive Section



// Drive Upload Form
// Only display this if the user has logged in using the API /drive/auth URL.

// I don't have permission to edit pikaDrive on dev server at the moment, this
// is a work around.
//if (pikaDrive::isAuthenticated($auth_row["username"]))
if (file_exists("/var/www/html/cms-custom/extensions/google_drive_connector/tokens/{$auth_row['username']}"))
{
	$C .= '
	<form method="post" enctype="multipart/form-data" action="">
	<div class="row">
		<div class="span5">
		<input type="file" name="file_upload">
		<input type="hidden" name="folder_id" value="' . $case1->google_drive_folder_id . '">
		<input type="submit" value="Upload">
		</div>
		<div class="span4">';

	// Add a "Log out of Google Drive" button.
	$C .= '<a class="btn btn-mini" title="Disconnect your Pika CMS account from Google Drive uploads" href="/api/v1/drive/unauthorize?username=' ;
	$C .= htmlspecialchars($auth_row['username']);
	$C .= '" target="_blank">X</a>';

	$C .= '
		</div>
	</div>
	</form>';

}

else
{
	$clean_username = htmlspecialchars($auth_row['username']);
	
	$C .= '<p>Please log your Pika CMS account into <a class="btn btn-mini"';
	$C .= ' onClick=\'window.open("/api/v1/drive/auth?username='.$clean_username.'", "Request for Authorization", "width=600, height=400, scrollbars=yes");\'';
	$C .= '>Google Drive</a> if you wish to upload documents to Drive through Pika CMS.';
	$C .= '  Please reload this page once you\'ve logged in.</p>';
}
// End Drive Upload Form

require_once(getcwd() . '-custom/extensions/google_drive_connector/file_list.php');
$a['file_list'] =  file_list($case1->google_drive_folder_id);
$template = new pikaTempLib('subtemplates/case-drive.html', $a);
$C .= $template->draw();


// Handle Drive uploads
// TODO Move this elsewhere

if (array_key_exists('file_upload', $_FILES))
{
	$rest = new PikaDrive($auth_row['username']);
	$y = $rest->uploadFile($_FILES['file_upload']['tmp_name'], $_FILES['file_upload']['name'], $case1->google_drive_folder_id);  
	// $_POST['title'], $_POST['folder_id'])	
}

}


?>
