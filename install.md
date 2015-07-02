# Server install instructions for the Google Apps Integration project

## Requirements

* Google Apps for Business account, with Drive enabled for all users.
* Pika CMS 6.04 or higher.
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

### Setting up the Source Code Files

*  Create the folders /var/www/html/api and /var/www/html/api/v1.

*  Download the project files off of github.  

*  Copy the project file api/v1/index.php to /var/www/html/api/v1/index.php.

*  Change line 18 in /var/www/html/api/v1/index.php to match the folder name of
your site folder.

*  Change line 19 in /var/www/html/api/v1/index.php to match your time zone.

*  Copy the project files in cms-custom/ to your Pika CMS custom folder.  If you
have customized your templates/default.html file, you'll need to merge the 
project's changes to this file with your existing changes.

*  Log into Pika CMS with an account with system-level permissions.  Go to Site
Map -> Extensions (under System Settings).  Enable the Google Drive integration
extension.

*  Next, while still logged into Pika CMS, go to Site Map -> Case Tab Manager 
(under System Settings).  Add a new case tab for the Drive file listing, it will 
use case-drive.php.

### Allowing Google Drive API Connections

* At Google Developers Console, <https://console.developers.google.com/>, create 
or select an existing project.

* In side bar on the left, select Api & Auth > API.

* Make sure that Drive API is "ON".

* In side bar on the left, select Api & Auth > Credentials.

* Create a new Client ID.

* Application type: **Web Application**

* Authorised Javascript Origins: **(your site url)**

* Authorised Redirect URIS: **(your site url)**/api/v1/drive/auth

* Make a note of your Client ID and Client Secret values, you will need them
later.

### Creating a Google Drive Folder to Store the Documents

*  Create your program's root CMS documents folder in Google Drive.  This needs 
to be a Drive folder to which everyone in the organization has read and write 
privileges.  The folder can be in any user's account.  

*  Determine the unique ID of of the root Google Drive folder you just created.  
You can retrieve the ID by navigating to the folder from the Google Drive 
website, and copying the unique ID from the URL.  Here's an example; you want 
the characters after "folders/":

`https://drive.google.com/drive/u/1/folders/0B-ABCDEFGHI-fnRNRndBYWZMQ2ZRbHlJc3R0UVpoc3VqaThSOHpMb0lmUmtwZ0123456789`

*  Pull up the Client ID and Client Secret codes for your program's Google Apps
account provided by Google.

*  Create a blank file "google_drive_config.php" in 
"/var/www/html/api/v1/".  This is the Google Drive 
configuration file.  It should define the unique folder ID, Client ID and 
Client Secret is this format:
	
<?php

define("CLIENT_ID", '**abc123**');

define("CLIENT_SECRET", '**def456**');

define("UNIQUE_FOLDER_ID", '**0B-ABCDEFGHI-fnRNRndBYWZMQ2ZRbHlJc3R0UVpoc3VqaThSOHpMb0lmUmtwZ0123456789**');

?>

### Tickler Email Integration

* Change line 3 and 4 in custom/extensions/create_tickler/config.php to match 
a valid Gmail user and password.

* Change line 6 in custom/extensions/create_tickler/config.php to set the subject
line of tickler emails.  You could use %case% or %client% inside subject and it 
will be dynamically replaced by Case Number and Client Name.

* Optionally, change line 9 and 10 in custom/extensions/create_tickler/config.php to 
set the name and email that will appears on tickler emails.

* Optionally, edit custom/extensions/create_tickler/content.html to modify the 
email template.

* Run cms-custom/extensions/create_tickler/test.php to test the email configuration
without creating an actual tickler record.

### Registering Gmail Markup

* Send an email from the test page to schema.whitelisting+sample@gmail.com to start
the registration process.

* Fill out the registration form at <https://docs.google.com/a/google.com/forms/d/1PA-vjjk3yJF7MLPOVKbIz3MBfhyma2obS8NIZ0JYx8I/viewform?pli=1>.

* Is your email Promotional or has a Promotional Intent or is a Solicitation? "No"

* Which Structured Data Type do you plan to add to your email? "Action"

* Which Action do you plan to add to your email? "One-Click Action"

* If you plan to use a One-Click or Go-To Action, what text will appear on the button? "Add to Calendar"

* Wait for Google to approve your request.

For detailed information about the registration process, please read "Email Markup - Registering with Google" at <https://developers.google.com/gmail/markup/registering-with-google>.

### Modify the Database

*  Run the SQL file google_apps_api.sql on your Pika CMS database.

### Configure Apache Web Server

*  Create a blank file "google_apps_api.conf" in /etc/httpd/conf.d/.  Paste the following into it then save:

`<Directory /var/www/html/api>`

`RewriteEngine on`

`RewriteBase /api/v1/`

`RewriteCond %{REQUEST_FILENAME} !-f`

`RewriteCond %{REQUEST_FILENAME} !-d`

`RewriteRule ^(.+)$ index.php/$i [L,QSA]`

`</Directory>`

*  Restart apache by running "sudo systemctl restart httpd.service".

### End of Installation

The API will now be available at `https://(server name)/api/v1/`.