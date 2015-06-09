<?php

require_once('pika-danio.php');
pika_init();

require_once(getcwd() . '-custom/extensions/google_drive_connector/file_list.php');
echo file_list(htmlspecialchars($_GET['folder_id']),
				htmlspecialchars($_GET['prev_folder_id']),
				htmlspecialchars($_GET['root_folder_id']));

pika_exit();
	
?>