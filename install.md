# Server install instructions for the Google Apps Integration project

## Requirements

* Google Apps for Business account, with Drive enabled for all users.
* Pika CMS 6.0 or higher.
* CentOS version 7.  It should work OK on earlier versions of CentOS, or other
operating systems, but the httpd service syntax and file paths may be 
different than what appears in these instructions.
* SSL should be enabled on the web server.  A self-signed certificate will work fine.
* Your Apache web server Document Root directory needs to be /var/www/html/.
* Your Pika CMS files and the API files need to be installed to /var/www/html/.

## Caveats

* Google Drive does not enforce Pika CMS security level rules, so any documents
uploaded to Google Drive through the Pika CMS case tab will be visible to all
users, even if they don't have read access in Pika CMS to the case the document belongs to.

## Instructions

*  Log into your Pika CMS server.

*  Create the folders /var/www/html/api and /var/www/html/api/v1.

*  Download the project files off of github.  

*  Copy the project file api/v1/index.php to /var/www/html/api/v1/index.php.

*  Change line 18 in /var/www/html/api/v1/index.php to match the folder name of
your site folder.

*  Change line 19 in /var/www/html/api/v1/index.php to match your time zone.

*  Copy the project files in cms-custom/ to your Pika CMS custom folder.  If you
have customized your templates/default.html file, you'll need to merge the 
project's changes to this file with your existing changes.

*  Create your program's root Google Drive folder.  This needs to be a Drive folder to 
which everyone in the organization has read and write privileges.  The folder 
can be in any user's account.  

*  Determine the unique ID of of the root Google Drive folder you just created.  
You can retrieve the ID by navigating to the 
folder from the Google Drive website, and copying the unique ID from the URL.  
Here's an example; you want the characters in bold text:

`https://drive.google.com/drive/u/1/folders/**0B-ABCDEFGHI-fnRNRndBYWZMQ2ZRbHlJc3R0UVpoc3VqaThSOHpMb0lmUmtwZ0123456789**`

*  Pull up the Client ID and Client Secret codes for your program's Google Apps
account provided by Google.

*  Create a blank file "google_drive_config.php" in 
"custom/extensions/google_drive_connector/".  This is the Google Drive 
configuration file.  It should define the unique folder ID, Client ID and 
Client Secret is this format:
	
<?php
define("CLIENT_ID", '**abc123**');
define("CLIENT_SECRET", '**def456**');
define("UNIQUE_FOLDER_ID", '**0B-ABCDEFGHI-fnRNRndBYWZMQ2ZRbHlJc3R0UVpoc3VqaThSOHpMb0lmUmtwZ0123456789**');
?>

*  Run the SQL file google_apps_api.sql on your Pika CMS database.

*  Create a blank file "google_apps_api.conf" in /etc/httpd/conf.d/.  Paste the following into it then save:

`<Directory /var/www/html/api>
RewriteEngine on
RewriteBase /api/v1/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ index.php/$i [L,QSA]
</Directory>`

*  Restart apache by running "sudo systemctl restart httpd.service".

The API will now be available at `https://(server name)/api/v1/`.