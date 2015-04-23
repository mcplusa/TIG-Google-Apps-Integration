<?php


// Drive Listing Section - TODO move this to pikaCase and run it when creating a new case.
// ...or should it???  What happens if the intake person doesn't have Drive set
// up?

require_once(getcwd() . '-custom/extensions/google_drive_connector/index.php');

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

if (strlen($case1->google_drive_folder_id) == 0 && !file_exists(getcwd() . "-custom/extensions/google_drive_connector/tokens/{$auth_row['username']}"))
{
	// Bail out to prevent errors when folder_search_or_create is run.
	$clean_username = htmlspecialchars($auth_row['username']);
	
	$C .= '<p>This case does not have a Google Drive folder. you need to connect your Pika CMS account to your Google account. To do so, <a class=""';
	$C .= ' onClick=\'window.open("/api/v1/drive/auth?username='.$clean_username.'", "Request for Authorization", "width=450, height=500, scrollbars=yes");\'';
	$C .= '><strong>click here to log into Google Drive</strong></a>.</p>';
	$C .= '<p><em>Please reload or refresh this page once you have logged in</em>.</p>';
	
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
	$case_sub_folder_id = folder_search_or_create($case_sub_folder_name, UNIQUE_FOLDER_ID);
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
if (file_exists(getcwd() . "-custom/extensions/google_drive_connector/tokens/{$auth_row['username']}"))
{
	$C .= '
	<form method="post" enctype="multipart/form-data" action="">
	<div class="row file-uploads">
		<div class="span5">
		<input class="choose-file" type="file" name="file_upload">
		<input type="hidden" name="folder_id" id="upload_folder_id" value="' . $case1->google_drive_folder_id . '">
		<input type="submit" value="Upload">
		</div>
		<div class="span4 disconnect">';

	// Add a "Log out of Google Drive" button.
	$C .= '<a class="btn" title="Disconnect your Pika CMS account from Google Drive." href="/api/v1/drive/unauthorize?username=' ;
	$C .= htmlspecialchars($auth_row['username']);
	$C .= '" target="_blank">Disconnect Drive</a>';

	$C .= '
		</div>
	</div>
	</form>';

}

else
{
	$clean_username = htmlspecialchars($auth_row['username']);
	
	$C .= '<p>To upload and view case-related files, you need to connect your Pika CMS account to your Google account. To do so, <a class=""';
	$C .= ' onClick=\'window.open("/api/v1/drive/auth?username='.$clean_username.'", "Request for Authorization", "width=450, height=500, scrollbars=yes");\'';
	$C .= '><strong>click here to log into Google Drive</strong></a>.</p>';
	$C .= '<p><em>Please reload or refresh this page once you have logged in</em>.</p>';
}
// End Drive Upload Form

require_once(getcwd() . '-custom/extensions/google_drive_connector/file_list.php');
$a['file_list'] =  file_list($case1->google_drive_folder_id, null, $case1->google_drive_folder_id);
$template = new pikaTempLib('subtemplates/case-drive.html', $a);
$C .= $template->draw();


// Handle Drive uploads
// TODO Move this elsewhere

if (array_key_exists('file_upload', $_FILES))
{
	$rest = new PikaDrive($auth_row['username']);
	$y = $rest->uploadFile($_FILES['file_upload']['tmp_name'], $_FILES['file_upload']['name'], htmlspecialchars($_POST['folder_id']));  
	// $_POST['title'], $_POST['folder_id'])	
}

}


?>
