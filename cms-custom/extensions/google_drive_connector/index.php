<?php
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';


/* The Google Drive configuration file should define the client_id and 
	client_secret is this format:
	
	<?php
	define("CLIENT_ID", 'abc123');
	define("CLIENT_SECRET", 'def456');
	?>
*/
require_once('google_drive_config.php');

$url_array = explode('?', 'http://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
define("URL", $url_array[0]);
define("TOKENS_PATH", dirname(__FILE__) . '/' . "tokens/");


class PikaDrive {
  private $gClient;
  private $token;
  private $tokenPath = TOKENS_PATH;

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
    $tokenPath = $this->tokenPath . $username;

    if(isset($token) && !empty($token)){
      $this->token = $this->gClient->authenticate($token);
      file_put_contents($tokenPath, $this->token);
    }else{
      if(file_exists($tokenPath)){
        $this->token = file_get_contents($tokenPath);
        return true;
      }
    }

    return false;
  }

  function unauthorize($username){
    unlink($this->tokenPath . $username);
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
    $tokenPath = $this->tokenPath . $username;

	if(file_exists($tokenPath)){
	    return true;
	}

    return false;
  }

}
