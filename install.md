*  Create a blank file "google_apps_api.conf" in /etc/httpd/conf.d/.  Paste the following into it then save:

`<Directory /var/www/html/api>
RewriteEngine on
RewriteBase /api/v1/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ index.php/$i [L,QSA]
</Directory>`

*  Restart apache by running "sudo systemctl restart httpd.service".
*  Download the project files off of github.  Take the file api/v1/index.php and copy it to /var/www/html/api/v1/index.php on the server.  You'll probably need to create the api and v1 directories first.
*  Run the SQL file google_apps_api.sql.
*  Change line 14 to "$site_folder_name = 'cms';"

The API will now be available at `http[s]://(IP address)/api/v1/`, using the username "api" and the password "na5us+wr".