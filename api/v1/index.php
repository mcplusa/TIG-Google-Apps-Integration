<?php 


/*** WARNING:  I haven't added authentication yet, until it's
	added, this script makes your data available to anyone.
	Use with test data only.  ***.



/***************************/
/* (C) 2014                */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


$site_folder_name = 'cms';


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

switch($api_request[0]) 
{
	case 'case':
	
		$clean_case_id = mysql_real_escape_string($api_request[1]);
		$sql = "SELECT case_id, number AS case_number, NULL AS case_name, status as case_status FROM cases WHERE case_id={$clean_case_id}";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		header_send ();
		echo "[" . json_encode($row) . "]";
		break;

	case 'casenotes':
		$a = array();
		$clean_case_id = pl_grab_get('case_id');
		$sql = "SELECT act_id AS case_note_id, case_id, summary, notes FROM activities WHERE case_id={$clean_case_id}";
		$result = mysql_query($sql);
		
		while ($row = mysql_fetch_assoc($result))
		{
			$a[] = $row;
		}

		header_send ();
		echo json_encode($a);
		break;

	default:
	
		header("Content-Type text/php");
		echo LSNC_API_NAME . ' Version: ' . LSNC_API_VERSION . "." . LSNC_API_REVISION;
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
