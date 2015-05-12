<?php

$site_path = '';
$number_of_docs_to_migrate = 1;
$pika_cms_username = 'jsmith';

// Source Code Starts Here *********************************************

$config_path = $site_path . '-custom/config/settings.php';

if (!file_exists($config_path))
{
	echo "Could not find the config file.\n";
	exit();
}

require_once($config_path);

$library_path = $site_path . '-custom/extensions/google_drive_connector/index.php';
require_once($library_path);



function folder_search_or_create($folder_name, $parent_id, $username)
{
	//$drive = new PikaDrive($username);
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


$drive = new PikaDrive($pika_cms_username);

if (!$drive->check())
{
	echo "{$pika_cms_username} is not logged in to google drive.";
	exit();
}


mysql_connect($plSettings['db_host'], $plSettings['db_user'], $plSettings['db_password']);
mysql_select_db($plSettings['db_name']);


/*
	
add cases.google_drive_folder_id
add doc_storage.google_drive_path
ALTER TABLE doc_storage ADD COLUMN google_drive_path TEXT;
*/



/* create drive folder for all case_id's in SELECT case_id FROM doc_storage GROUP by case_id;
Save the unique_id
*/
$sql = "SELECT case_id FROM doc_storage LEFT JOIN cases USING(case_id) " 
	. "WHERE drive_unique_id IS NULL GROUP BY case_id";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result))
{
	$case_sub_folder_name = str_pad(substr($row['case_id'], -3), 3, '0', STR_PAD_LEFT);	
	// Look for the sub_folder where this case's folder will go.  If it doesn't
	// exist, create it.
	$case_sub_folder_id = folder_search_or_create($case_sub_folder_name, UNIQUE_FOLDER_ID, $pika_cms_username);
	// Now that the sub_folder_id has either been found or created, we can find
	// (if the folder has been orphaned somehow) or create the case's folder 
	// inside the case_sub_folder (which is inside the root folder).
	$case_folder_id = folder_search_or_create($row['case_id'], $case_sub_folder_id, $pika_cms_username);
	mysql_query("UPDATE cases SET google_drive_folder_id = '{$case_folder_id}' WHERE case_id = '{$row['case_id']}'");
}



/*  create drive folder for all subfolders
*/
$sql = "SELECT doc_id, case_id, doc_data, doc_name, google_drive_folder_id, folder_ptr FROM doc_storage "
	. "LEFT JOIN cases USING(case_id) "
	. "WHERE doc_storage.case_id IS NOT NULL AND doc_type='C' AND folder = '1' LIMIT 1";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result))
{	
	if ($row['folder_ptr'] > 0)
	{
		$sql0 = "SELECT google_drive_path FROM doc_storage WHERE doc_id = '{$row['folder_ptr']}'";
		$result0 = mysql_query($sql0);
		$row0 = mysql_fetch_assoc($result0);
		$x = $row0['google_drive_path'];
	}
	
	else
	{
		$x = $row['google_drive_folder_id'];
	}
	
	$x = $drive->createFolder($row['doc_name'], $x);
	mysql_query("UPDATE doc_storage SET google_drive_path = '{$x['id']}' WHERE doc_id = '{$row['doc_id']}'");	
}



/* Migrate all non-folder case docs.
*/
for ($i = 0; $i < $number_of_docs_to_migrate; $i++)
{
	$sql = "SELECT doc_id, case_id, doc_data, doc_name FROM doc_storage "
		. "WHERE case_id IS NOT NULL AND doc_type='C' AND folder = '0' LIMIT 1";
	$result = mysql_query($sql);
	
	if (mysql_num_rows($result) == 0)
	{
		echo "No more documents found in {$plSettings['db_name']}.\n"
		break;
	}
	
	$row = mysql_fetch_assoc($result);
	file_put_contents("/tmp/{$row['doc_name']}", $row['doc_data']);
	//$drive = new PikaDrive($pika_cms_username);
	echo $drive->uploadFile("/tmp/{$row['doc_name']}", $row['doc_data'], $_POST['folder_id']);
	unlink("/tmp/{$row['doc_name']}");
	echo "\n";
	echo $row['doc_id'] . "\n";
}

$sql = "SELECT COUNT(*) AS a FROM doc_storage";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo $row['a'];



exit();
	
?>