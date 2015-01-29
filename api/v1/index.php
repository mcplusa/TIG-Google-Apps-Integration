<?php 


/*** WARNING:  I haven't added authentication yet, until it's
	added, this script makes your data available to anyone.
	Use with test data only.  ***.



/***************************/
/* (C) 2014                */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


// Config variables 
$site_folder_name = 'cms';
$time_zone = 'America/New_York';


// Definitions
define('LSNC_API_NAME','LSNC Google Apps API');
define('LSNC_API_VERSION','1');
define('LSNC_API_REVISION','0');


// CORS HTTP headers
header('Access-Control-Allow-Origin: https://mail.google.com');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: accept, authorization, content-type');
header('Access-Control-Allow-Methods: GET,HEAD,PUT,PATCH,POST,DELETE');


// Database variables
$plSettings = array();
include('../../' . $site_folder_name . '-custom/config/settings.php');
$db_host = $plSettings['db_host'];
$db_name = $plSettings['db_name'];
$db_user = $plSettings['db_user'];
$db_password = $plSettings['db_password'];


// Application initialization
$status = mysql_connect($db_host, $db_user, $db_password);

if ($status !== false)
{
	$connection_is_live = mysql_select_db($db_name) or trigger_error(mysql_error());
	mysql_query("SET SESSION query_cache_type = DEMAND") or trigger_error(mysql_error());
}

set_time_limit(0);
ini_set('memory_limit','999M');

if (function_exists('date_default_timezone_set')) 
{
	if (!$time_zone) 
	{
		$time_zone = 'America/New_York';
	}
	
	date_default_timezone_set($time_zone);
}


// Functions
function header_send()
{
    header("Access-Control-Allow-Origin: https://mail.google.com");
    header("Cache-Control:  no-cache");
    header("Content-Type:  application/json; charset=UTF-8");
    header("Expires:  Fri, 01 Jan 1990 00:00:00 GMT");
}

function server_error($message)
{
	http_response_code(500);
	echo json_encode(array($message));
	exit();
}

function post_value($field)
{
	$json = file_get_contents('php://input');
	$v = json_decode($json, TRUE);
	
	if (!is_array($v))
	{
		server_error("Invalid JSON input.");
	}
	
	if (array_key_exists($field, $v))
	{
		return "'" . mysql_real_escape_string($v[$field]) . "'";
	}
	
	else
	{
		return "NULL";
	}		
}

function get_value($field)
{
	if (isset($_GET[$field]))
	{
		return mysql_real_escape_string($_GET[$field]);
	}
	
	else return null;
}

function get_value_numeric($field)
{
	if (isset($_GET[$field]))
	{
		if (is_numeric($_GET[$field]))
		{
			return mysql_real_escape_string($_GET[$field]);
		}
		
		else
		{
			http_response_code(400);
			exit();
		}
	}
	
	else return null;
}

function next_id($sequence)
{
	// VARIABLES
	$safe_sequence = mysql_escape_string($sequence);
	$next_id = null;
	
	mysql_query("LOCK TABLES counters WRITE") or trigger_error('counters table lock failed');
	$result = mysql_query("SELECT count FROM counters WHERE id = '{$safe_sequence}' LIMIT 1")
		or trigger_error('');
	
	if (mysql_num_rows($result) < 1)
	{
		mysql_query("INSERT INTO counters SET id = '{$safe_sequence}', count = '1'")
		or trigger_error('');
		$next_id = 1;
	}
	
	else
	{
		$row = mysql_fetch_assoc($result);
		$next_id = $row['count'] + 1;
		
		mysql_query("UPDATE counters SET count = count + '1' WHERE id = '{$safe_sequence}' LIMIT 1")
			or trigger_error('error_during_increment');
	}
	
	mysql_query("UNLOCK TABLES") or trigger_error('error');
	return $next_id;
}

// From http://php.net/manual/en/function.http-response-code.php
if (!function_exists('http_response_code')) 
{
    function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        return $code;
    }
}


// Classes

/**
* Base class for processing REST requests.
*/
class restResource 
{
	protected $table = 'db_table';
	protected $get_sql = "SELECT columns FROM db_table WHERE case_id=";
	//protected $count_sql = "SELECT COUNT(*) AS row_count FROM db_table WHERE id=";
	protected $data_fields = 'id, a, b, c';
	protected $id = null;
	
	function __construct($id = null)
	{
		$this->id = $id;
	}
	
	function transmitJson()
	{
		$clean_id = mysql_real_escape_string($this->id);
		$sql = $this->get_sql . $clean_id;
		$result = mysql_query($sql) or server_error(mysql_error($result));
		
		if (mysql_num_rows($result) == 0)
		{
			http_response_code(404);  // NOT FOUND
			exit();
		}
		
		else if (mysql_num_rows($result) > 1)
		{
			server_error(mysql_error($result));  // INTERNAL SERVER ERROR
		}
		
		else 
		{
			$row = mysql_fetch_assoc($result);
			header_send();
			echo "[" . json_encode($row) . "]";
		}		
	}
	
	function post_values()
	{
		return '';
	}
	
	function post()
	{
		$this->id = next_id($this->table);
		$sql = "INSERT INTO {$this->table} ({$this->data_fields}) VALUES (";
		$sql .= $this->id . ", " . $this->post_values();
		$sql .= ")";
		
		$result = mysql_query($sql) or server_error(mysql_error($result));
		
		$this->transmitJson();
		
		return;
	}

	function get()
	{
		$this->transmitJson();
		
		return;
	}

	function put()
	{
		http_response_code(500);  // Server Error; placeholder
		// If exists, update case; if not, error
		
		/*
		$clean_id = mysql_real_escape_string($this->id);
		$sql = $this->count_sql . $clean_id;
		$result = mysql_query($sql) or http_response_code(500);
		$row = mysql_fetch_assoc($result);
		
		if ($row['row_count'] == 1)
		{
			$sql = "UPDATE {$this->table} SET ";
			$z = array();
			
			foreach ($this->data_fields as $key => $value)
			{
				$z[] = null;
			}
			
		}
		
		return;
		*/
	}

	function delete()
	{
		http_response_code(405);  // NOT ALLOWED
		return;
	}
}


/**
* REST resource that returns a JSON list.
*/
class restResourceList extends restResource 
{
	protected $get_sql = "SELECT columns FROM db_table";
	
	function post()
	{
		http_response_code(500);  // Server Error; placeholder
		// Create new case.				
		return;		
	}

	function get($extra_sql = '')
	{
		$safe_limit = 10;
		$safe_offset = 0;
		
		$tmp_offset = get_value_numeric('offset');
		
		if ($tmp_offset)
		{
			$safe_offset = $tmp_offset;
		}
		
		$tmp_limit = get_value_numeric('limit');
		
		if ($tmp_limit)
		{
			$safe_limit = $tmp_limit;		
		}

		$sql = $this->get_sql . " {$extra_sql} LIMIT {$safe_offset}, {$safe_limit}";
		$result = mysql_query($sql);
		$a = array();
		
		while (	$row = mysql_fetch_assoc($result))
		{
			$a[] = $row;
		}
		
		header_send ();
		echo json_encode($a);
		return;
	}
	
	function put()
	{
		http_response_code(405);  // NOT ALLOWED
		return;		
	}
}



/**
* Request for a list of cases.
*/
class restCaseList extends restResourceList 
{
	protected $table = 'cases';
	protected $get_sql = "SELECT case_id, number AS case_number, NULL AS case_name, CONCAT(contacts.last_name, ', ', IFNULL(contacts.first_name, '')) as client_name, status as case_status, CONCAT(users.last_name, ', ', users.first_name) AS advocate FROM cases LEFT JOIN contacts ON cases.client_id=contacts.contact_id LEFT JOIN users ON cases.user_id=users.user_id";
	
	function get()
	{
		$extra_sql = '';
		$safe_q = get_value('q');
		$safe_u = get_value('u');

		if (($safe_q) || ($safe_u))
		{
			$extra_sql = " WHERE number LIKE '%{$safe_q}%'";
			if($safe_u)
				$extra_sql.= " and us.username = '{$safe_u}'";
		}

		parent::get($extra_sql);
	}
}


/**
* Request for a single case.
*/
class restCase extends restResource 
{
	protected $table = 'cases';
	protected $get_sql = "SELECT case_id, number AS case_number, NULL AS case_name, CONCAT(contacts.last_name, ', ', IFNULL(contacts.first_name, '')) as client_name, status as case_status, CONCAT(users.last_name, ', ', users.first_name) AS advocate FROM cases LEFT JOIN contacts ON cases.client_id=contacts.contact_id LEFT JOIN users ON cases.user_id=users.user_id WHERE case_id=";
	protected $data_fields = 'case_id, number, status';

	function post_values()
	{
		$x = post_value('case_number') . ', 1';
		
		return $x;
	}
}


/**
* Request for a list of case notes.
*/
class restCaseNoteList extends restResourceList 
{
	protected $table = 'activities';
	protected $get_sql = "SELECT act_id AS case_note_id, case_id, summary, activities.notes AS notes, hours, CONCAT(users.last_name, ', ', users.first_name) AS staff, funding AS funding_source FROM activities LEFT JOIN users ON activities.user_id=users.user_id WHERE case_id IS NOT NULL";
	
	function get()
	{
		$extra_sql = '';
		$safe_case_id = get_value_numeric('case_id');
		
		if ($safe_case_id)
		{
			$extra_sql = "AND case_id = {$safe_case_id}";
		}
		
		parent::get($extra_sql);
	}
}


/**
* Request for a single case note.
*/
class restCaseNote extends restResource 
{
	protected $table = 'activities';
	protected $get_sql = "SELECT act_id AS case_note_id, case_id, summary, notes, hours, funding AS funding_source FROM activities WHERE act_id=";
	protected $data_fields = 'act_id, case_id, summary, notes, act_date, act_time, act_type, last_changed';

	
	function post_values()
  {
      $x = post_value("case_id") . ",";
      $x .= post_value("summary") . ",";
      $x .= post_value("notes") . ",";
      $x .= "'" . date("Y-n-j") . "',";
      $x .= "'" . date("H:i:s") . "',";
      $x .= "'N',";
      $x .= "'" . date("Y-n-j") . " " . date("H:i:s") . "'";
      
      return $x;
  }
}
//mysql_connect(DB_HOST,DB_USER,DB_PASS);
//mysql_select_db(DB_NAME);

// OAuth authentication
/*
$http_headers = apache_request_headers();

if (!isset($http_headers['Authorization']))
{
	http_response_code(401);
	echo "token not sent";
	return false;
}

$token = $http_headers['Authorization'];
$token = str_replace('Bearer ', '', $token);
$clean_token = mysql_real_escape_string($token);

if (strlen($clean_token) != 40)
{
	http_response_code(401);
	echo "token not 40 chars";
	return false;
}

$sql = "SELECT user_id AS row_count FROM oauth_token WHERE type=2 AND token='{$clean_token}'";
$result = mysql_query($sql) or server_error("An error was encountered.");

if (mysql_num_rows($result) != 1)
{
	http_response_code(401);
	exit();
}
*/


// HTTP Authentication
if (!isset($_SERVER['PHP_AUTH_USER'])) 
{
    
    header('WWW-Authenticate: Basic realm="' . LSNC_API_NAME . '"');
    header('HTTP/1.0 204 No Content');
    echo 'HTTP/1.0 204 No Content';
    exit;
}

else if (!isset($_SERVER['PHP_AUTH_PW'])) 
{
	header('WWW-Authenticate: Basic realm="' . LSNC_API_NAME . '" stale="FALSE"');
	header('HTTP/1.0 401 Unauthorized');
	exit();
}

else 
{
	$safe_username = mysql_real_escape_string($_SERVER['PHP_AUTH_USER']);
	$safe_password_hash = mysql_real_escape_string(md5($_SERVER['PHP_AUTH_PW']));
	
	$sql = "SELECT user_id FROM users WHERE enabled=1 AND username='{$safe_username}' AND password='{$safe_password_hash}'";
	$result = mysql_query($sql) or server_error("An error was encountered.");
	
	if (mysql_num_rows($result) != 1)
	{
		header('WWW-Authenticate: Basic realm="' . LSNC_API_NAME . '" stale="FALSE"');
		header('HTTP/1.0 401 Unauthorized');
		exit();
	}
}


// Main code
$question_position = strpos($_SERVER['REQUEST_URI'], '?');
if ($question_position)
{
	$uri = substr($_SERVER['REQUEST_URI'], 0, $question_position);
}

else
{
	$uri = $_SERVER['REQUEST_URI'];
}

$api_request = explode('/', $uri);
array_shift($api_request);  //  Remove '/'
array_shift($api_request);  //  Remove 'api/'
array_shift($api_request);  //  Remove 'v1/'
/*	If the URL has a trailing '/', an empty element will be tacked on the end of the $api_request
	array.  Clean this up with the following code.
*/
if ('' == $api_request[sizeof($api_request) - 1])
{
	array_pop($api_request);
}

/*
if (sizeof($api_request) == 1)
{
	switch($api_request[0]) 
	{
		case 'cases':
			
			$rest = new restCaseList();
			break;
		
		case 'casenotes':
			$rest = new restCaseNote();
			break;
		
		default:
			http_response_code(404);  // NOT FOUND
			break;
	}
}

else if (sizeof($api_request) == 2)
{
	switch($api_request[0]) 
	{
		case 'cases':
			$rest = new restCase($api_request[1]);
			break;
		
		case 'casenotes':
			$rest = new restCaseNote($api_request[1]);
			break;
		
		default:
			http_response_code(404);  // NOT FOUND
			break;
	}
	
}

else
{
	http_response_code(400);  // BAD REQUEST
}

switch ($_SERVER['REQUEST_METHOD'])
{
	case 'POST':
		$rest->post();
		break;
		
	case 'GET':
		$rest->get();
		break;
		
	case 'PUT':
		$rest->put();
		break;
	
	case 'DELETE':
		$rest->delete();
		break;
	
	default:
		http_response_code(400);  // BAD REQUEST
		break;
}
*/

if ('cases' == $api_request[0])
{
	if (sizeof($api_request) == 1)
	{
		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'POST':
				$rest = new restCase();
				$rest->post();
				break;
				
			case 'GET':
				$rest = new restCaseList();
				$rest->get();
				break;

			default:
				http_response_code(400);  // BAD REQUEST
				break;
		}
	}
	
	else if (sizeof($api_request) == 2)
	{
		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'POST':
				http_response_code(400);  // BAD REQUEST
				break;
				
			case 'GET':
				$rest = new restCase($api_request[1]);
				$rest->get();
				break;
				
			case 'PUT':
				$rest = new restCase($api_request[1]);
				$rest->put();
				break;
			
			default:
				http_response_code(400);  // BAD REQUEST
				break;
		}
	}

	else
	{
		http_response_code(400);  // BAD REQUEST
	}
}

else if ('casenotes' == $api_request[0])
{
	if (sizeof($api_request) == 1)
	{
		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'POST':
				$rest = new restCaseNote();
				$rest->post();
				break;
				
			case 'GET':
				$rest = new restCaseNoteList();
				$rest->get();
				break;

			default:
				http_response_code(400);  // BAD REQUEST
				break;
		}
	}
	
	else if (sizeof($api_request) == 2)
	{
		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'POST':
				http_response_code(400);  // BAD REQUEST
				break;
				
			case 'GET':
				$rest = new restCaseNote($api_request[1]);
				$rest->get();
				break;
				
			case 'PUT':
				$rest = new restCaseNote($api_request[1]);
				$rest->put();
				break;
			
			case 'DELETE':
				$rest = new restCaseNote($api_request[1]);
				$rest->delete();
				break;
			
			default:
				http_response_code(400);  // BAD REQUEST
				break;
		}
	}

	else
	{
		http_response_code(400);  // BAD REQUEST
	}
}

else
{
	http_response_code(400);  // BAD REQUEST
}

exit();


switch($api_request[0]) 
{
	case 'cases':
		
		if (array_key_exists(1, $api_request) && is_numeric($api_request[1])) /*  /api/v1/cases/1234 */
		{
			switch ($_SERVER['REQUEST_METHOD'])
			{
				case 'POST':
					http_response_code(400);  // BAD REQUEST
					break;
					
				case 'GET':
					$clean_case_id = mysql_real_escape_string($api_request[1]);
					$sql = "SELECT case_id, number AS case_number, NULL AS case_name, status as case_status FROM cases WHERE case_id={$clean_case_id}";
					$result = mysql_query($sql);
					
					if (mysql_num_rows($result) == 0)
					{
						http_response_code(404);  // NOT FOUND
					}
					
					else if (mysql_num_rows($result) > 1)
					{
						server_error(mysql_error($result));  // INTERNAL SERVER ERROR
					}
					
					else 
					{
						$row = mysql_fetch_assoc($result);
						header_send ();
						echo "[" . json_encode($row) . "]";
					}
					
					break;					
				
				case 'PUT':
					http_response_code(500);  // Server Error; placeholder
					// If exists, update case; if not, error				
				
				case 'DELETE':
					http_response_code(405);  // NOT ALLOWED
					break;
				
				default:
					http_response_code(400);  // BAD REQUEST
					break;
			}			
		}
		
		else /* /api/v1/cases */
		{
			switch ($_SERVER['REQUEST_METHOD'])
			{
				case 'POST':
					http_response_code(500);  // Server Error; placeholder
					// Create a new case
					break;
					
				case 'GET':
					// List cases
					$sql = "SELECT case_id, number AS case_number, NULL AS case_name, status as case_status FROM cases";
					$result = mysql_query($sql);
					$a = array();
					
					while (	$row = mysql_fetch_assoc($result))
					{
						$a[] = $row;
					}
					
					header_send ();
					echo json_encode($a);
					break;					
				
				case 'PUT':
				case 'DELETE':
					http_response_code(405);  // NOT ALLOWED
					break;
				
				default:
					http_response_code(400);  // BAD REQUEST
					break;
			}			
		}
		
		break;

	case 'casenotes':
	
		if (array_key_exists(1, $api_request) && is_numeric($api_request[1])) /*  /api/v1/casenotes/1234 */
		{
			switch ($_SERVER['REQUEST_METHOD'])
			{
				case 'POST':
					http_response_code(400);  // BAD REQUEST
					break;
					
				case 'GET':
					$a = array();
					$clean_act_id = mysql_real_escape_string($api_request[1]);
					$sql = "SELECT act_id AS case_note_id, case_id, summary, notes FROM activities WHERE act_id={$clean_act_id}";
					$result = mysql_query($sql);
					
					if (mysql_num_rows($result) == 0)
					{
						http_response_code(404);  // NOT FOUND
					}
					
					else if (mysql_num_rows($result) > 1)
					{
						server_error(mysql_error($result));  // SERVER ERROR;
						break;
					}
	
					else 
					{
						$row = mysql_fetch_assoc($result);
						header_send ();
						echo "[" . json_encode($row) . "]";
					}
					
					break;					
				
				case 'PUT':
					http_response_code(500);  // SERVER ERROR; place holder
					// If note exists, update.  If not, error.
					break;

				case 'DELETE':
					http_response_code(500);  // SERVER ERROR; place holder
					// Delete note.
					break;
				
				default:
					http_response_code(400);  // BAD REQUEST
					break;
			}
		}

		else /* /api/v1/casenotes */
		{
			switch ($_SERVER['REQUEST_METHOD'])
			{
				case 'POST':
					http_response_code(500);  // SERVER ERROR; place holder
					// create new case note.
					break;
					
				case 'GET':
					$a = array();
					$sql = "SELECT act_id AS case_note_id, case_id, summary, notes FROM activities WHERE case_id IS NOT NULL LIMIT 1000";
					$result = mysql_query($sql);
					
					if (mysql_num_rows($result) == 0)
					{
						http_response_code(404);  // NOT FOUND
					}
	
					else 
					{
						while ($row = mysql_fetch_assoc($result))
						{
							$a[] = $row;
						}
				
						header_send ();
						echo json_encode($a);
					}
					
					break;					
				
				case 'PUT':
				case 'DELETE':
					http_response_code(405);  // NOT ALLOWED
					break;
				
				default:
					http_response_code(400);  // BAD REQUEST
					break;
			}
		}
	
	default:
	
		http_response_code(404);  // NOT FOUND
		break;
}

function output_response($format = 'serialized', $response)
{
	switch ($format)
	{
		case 'json':
			header("Content-Type application/json; charset=UTF-8");
			echo json_encode($response);
			break;
		default:
			header("Content-Type text/php");
			echo serialize($response);
			break;
	}
	exit();
}

function create_response($data = null, $action = null, $response_code = 1, $response_description = "Operation Completed Successfully")
{
	$response = array();
	$response['request_method'] = $_SERVER['REQUEST_METHOD'];
	$response['action'] = $action;
	$response['response_code'] = $response_code;
	$response['response_description'] = $response_description;
	$response['response'] = $data;
	$response['username'] = '';
	if (isset($_SERVER['PHP_AUTH_USER']))
	{
		$response['username'] = $_SERVER['PHP_AUTH_USER'];
	}
	
	return $response;
}



?>
