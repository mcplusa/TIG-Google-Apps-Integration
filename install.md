# Server install instructions for the Google Apps Integration project

## Requirements

Google Apps for Business with Drive enabled for all users
SSL
CentOS 7

## Caveats

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

*  Change line 10 in custom/modules/case-drive.php to match the unique ID of 
your program's root Google Drive folder.  This needs to be a Drive folder to 
which everyone in the organization has read and write privileges.  The folder 
can be in any user's account.  You can retrieve the ID by navigating to the 
folder from the Google Drive website, and copying the unique ID from the URL.  
Here's an example; you want the characters in bold text:

`https://drive.google.com/drive/u/1/folders/**0B-ABCDEFGHI-fnRNRndBYWZMQ2ZRbHlJc3R0UVpoc3VqaThSOHpMb0lmUmtwZ0123456789**`

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