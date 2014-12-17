<?php 


/*** WARNING:  I haven't added authentication yet, until it's
	added, this script makes your data available to anyone.
	Use with test data only.  ***.



/***************************/
/* (C) 2014                */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


$site_folder_name = 'pika_cms/cms';


define('LSNC_API_NAME','LSNC Google Apps API');
define('LSNC_API_VERSION','1');
define('LSNC_API_REVISION','0');

$path = '../../' . $site_folder_name;
chdir($path);


$include_str = './app/lib' . PATH_SEPARATOR . './app/extralib' . PATH_SEPARATOR . ini_get('include_path');
ini_set('include_path', $include_str);

require_once('pl.php');

pl_mysql_init();
set_time_limit(0);
ini_set('memory_limit','999M');

if(function_exists('date_default_timezone_set')) 
{
	$time_zone = pl_settings_get('time_zone');
	
	if (!$time_zone) 
	{
		$time_zone='America/New_York';
	}
	
	date_default_timezone_set($time_zone);
}

function header_send ()
{
	header("Cache-Control:  no-cache");
	header("Content-Type:  application/json; charset=UTF-8");
	header("Expires:  Fri, 01 Jan 1990 00:00:00 GMT");
}


function pl_grab_req($var_name = null,$default_value = null)
{
	$request = array();
	switch ($_SERVER['REQUEST_METHOD'])
	{
		case 'GET':
			$request = $_GET;
			break;
		case 'POST':
		case 'PUT':
			parse_str(file_get_contents('php://input'),$request);
			break;
	}
	$value = isset($request[$var_name]) ? $request[$var_name] : $default_value;
	return $value;
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


/**
* Base class for processing REST requests.
*/
class restResource 
{
	private $table = 'cases';
	private $get_sql = "SELECT case_id, number AS case_number, NULL AS case_name, status as case_status FROM cases WHERE case_id=";
	
	function post($id)
	{
		http_response_code(400);  // BAD REQUEST
		return;
	}

	function get($id)
	{
		$clean_id = mysql_real_escape_string($id);
		$sql = $get_sql . $clean_id;
		$result = mysql_query($sql);
		
		if (mysql_num_rows($result) == 0)
		{
			http_response_code(404);  // NOT FOUND
		}
		
		else if (mysql_num_rows($result) > 1)
		{
			http_response_code(500);  // INTERNAL SERVER ERROR
		}
		
		else 
		{
			$row = mysql_fetch_assoc($result);
			header_send ();
			echo "[" . json_encode($row) . "]";
		}
		
		return;
	}

	function put($id)
	{
		http_response_code(500);  // Server Error; placeholder
		// If exists, update case; if not, error				
		return;		
	}

	function delete($id)
	{
		http_response_code(405);  // NOT ALLOWED
		break;
	}
}


//mysql_connect(DB_HOST,DB_USER,DB_PASS);
//mysql_select_db(DB_NAME);

/*
if (!isset($_SERVER['PHP_AUTH_USER'])) 
{
    header('WWW-Authenticate: Basic realm="' . LSNC_API_NAME . '"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'HTTP/1.0 401 Unauthorized';
    exit;
} 
else {
   	$safe_password_md5 = mysql_real_escape_string(md5($_SERVER['PHP_AUTH_PW']));
   	$safe_username = mysql_real_escape_string($_SERVER['PHP_AUTH_USER']);
	$sql = "SELECT organizations.* 
			FROM organizations 
			WHERE 1 
			AND username='{$safe_username}' 
			AND password='{$safe_password_md5}' 
			LIMIT 1";
	$result = mysql_query($sql);
	if(mysql_num_rows($result) != 1)
	{
		header('WWW-Authenticate: Basic realm="' . LSNC_API_NAME . '" stale="FALSE"');
		header('HTTP/1.0 401 Unauthorized');
		exit();
	}
	else
	{
		$auth_row = mysql_fetch_assoc($result);
	}
}
*/

$api_request = explode('/', $_SERVER['REQUEST_URI']);
array_shift($api_request);
array_shift($api_request);
array_shift($api_request);

/*	If the URL has a trailing '/', an empty element will be tacked on the end of the $api_request
	array.  Clean this up with the following code.
*/
if ('' == $api_request[sizeof($api_request) - 1])
{
	array_pop($api_request);
}


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
						http_response_code(500);  // INTERNAL SERVER ERROR
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
						http_response_code(500);  // SERVER ERROR;
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
