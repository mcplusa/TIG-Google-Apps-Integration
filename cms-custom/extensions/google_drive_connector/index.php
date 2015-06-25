<?php
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';


/* The Google Drive configuration file should define the client_id and 
	client_secret is this format:
	
	<?php
	define("CLIENT_ID", 'abc123');
	define("CLIENT_SECRET", 'def456');
	define("UNIQUE_FOLDER_ID", "ghi789");
	?>
*/
require_once('/var/www/html/api/v1/google_drive_config.php');

$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
define("URL", $url_array[0]);

class PikaDrive {
  private $gClient;
  private $token;

  function __construct($username = null){
    $this->gClient = new Google_Client();
    $this->gClient->setClientId(CLIENT_ID);
    $this->gClient->setClientSecret(CLIENT_SECRET);
    $this->gClient->setRedirectUri(URL);
    $this->gClient->setScopes(array('https://www.googleapis.com/auth/drive'));

    if($username != null && self::setToken($username)){
      self::authenticate();
    }
  }

  function setToken($username, $token = ''){
	$clean_token = mysql_real_escape_string($token);
	$clean_username = mysql_real_escape_string($username);
	
    if(isset($token) && !empty($token)){
      $this->token = $this->gClient->authenticate($token);
      $result = mysql_query("UPDATE users SET google_drive_token='{$clean_token}' WHERE username='{$clean_username}'");
    }else{
      $result = mysql_query("SELECT google_drive_token FROM users WHERE username='{$clean_username}'");
      $row = mysql_fetch_assoc($result);
      $this->token = $row['google_drive_token'];
      return true;
    }

    return false;
  }

  function unauthorize($username){
	$clean_username = mysql_real_escape_string($username);
    $result = mysql_query("UPDATE users SET google_drive_token=NULL WHERE username='{$clean_username}'");
  }

  function authenticate(){
    if (!isset($this->token)) {
      $this->gClient->authenticate();
    } else {
      $this->gClient->setAccessToken($this->token);
    }
  }

  function check(){
    return isset($this->token);
  }

  function createFolder($folderName, $parentId = null){
    return self::uploadFile("", $folderName, $parentId);
  }

  function uploadFile($filePath = "", $fileName, $folderId = null){
    if($fileName == null){
      $fileName = basename($filePath);
    }
    $data = "";

    $file = new Google_DriveFile();
    $file->setTitle($fileName);
    if($filePath){
      $file->setMimeType('');
      $data = file_get_contents($filePath);
    }else{
      $file->setMimeType('application/vnd.google-apps.folder');
    }

    if ($folderId != null) {
      $folders = explode(",", $folderId);
      $parents = array();
      foreach ($folders as $folder) {
        $parent = new Google_ParentReference();
        $parent->setId($folder);
        array_push($parents, $parent);
      }
      $file->setParents($parents);
    }

    $service = new Google_DriveService($this->gClient);

    $createdFile = $service->files->insert($file, array(
      'data' => $data,
      'mimeType' => ''
    ));

    return $createdFile;
  }

  function generateQueryString($q, $param, $value){
    if($q){
      $q .= " and ";
    }
    $q .= $param;
    return str_replace('?', $value, $q);
  }

  function listFiles($folderId = "", $trashed = false){
    $parameters = "";
    $q = "";
    
    if($folderId)
      $q = self::generateQueryString($q, "'?' in parents", $folderId);
    if(!$trashed)
      $q = self::generateQueryString($q, "trashed = ?", "false");

    $parameters['q'] = $q;

    $service = new Google_DriveService($this->gClient);
    $files = $service->files->listFiles($parameters);

    return $files['items'];
  }
  
  function isAuthenticated($username){
	$clean_username = mysql_real_escape_string($username);
    $result = mysql_query("SELECT google_drive_token FROM users WHERE username='{$clean_username}' AND google_drive_token IS NOT NULL");
    
    if (mysql_num_rows($result) == 1)
    {
	    return true;
    }

    return false;
  }

}
