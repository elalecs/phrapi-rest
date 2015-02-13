<?php
/**
 * PHP Raccoon Framework for APIs by Tecnologias Web de Mexico S.A. de C.V.
 *
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 */

file_exists('phrapi/validation.php') AND include_once 'phrapi/validation.php';
include_once 'phrapi/globals.php';

// No config? no service!
file_exists('config.php') OR status_code(500, "Config file does not exists!");

include_once 'config.php';

// No config array? no service!
isset($config) OR status_code(500, "Configuration not defined!");

// Offline? no service!
!$config['offline'] OR status_code(503);

// Load the actual server configuration
if(isset($config['servers'][$_SERVER['SERVER_NAME']])) {
	foreach($config['servers'][$_SERVER['SERVER_NAME']] as $param_key => $param_value) {
		$config[$param_key] = $param_value;
	}
	unset($config['servers']);
}

$config['base_path'] = dirname($_SERVER['SCRIPT_FILENAME']);
$config['controllers_path'] = $config['base_path'] . DS .  "controllers" . DS;

// There are hosting services where differs the LOCALE from MySQL to the PHP
// in that case it can be defined in the config array
if (isset($config['php_locale']) && empty($config['php_locale'])) {
	setlocale(LC_MONETARY | LC_NUMERIC, $config['php_locale']);
}

$params = [
	'controller' => '',
	'action' => '',
	'method' => strtolower($_SERVER['REQUEST_METHOD']),
	'credential' => []
];
$params += getStringFrom($_GET, 'resource', '', function($value) {
	$resource = [];
	$value = preg_replace('/^\/|\/$/', '', $value);
	$tokens = explode('/', $value);
	switch(sizeof($tokens)) {
		case 1:
			$resource = [
				'resource' => $tokens[0],
				'resource_id' => null
			];
			break;
		case 2:
			$resource = [
				'resource' => $tokens[0],
				'resource_id' => $tokens[1]
			];
			break;
	}
	return $resource;
});

$access_code = trim(getValueFrom($_REQUEST, 'access_code', '', FILTER_SANITIZE_STRING));
if (empty($access_code) OR !isset($config['credentials'][$access_code])) {
	//D($access_code);
	status_code(401);
} else {
	$params['credential'] = $config['credentials'][$access_code];
}

if (isset($params['credential']['debug']) && $params['credential']['debug'] === true) {
	define('DEBUGMODE',true);
}

if(
	empty($params['resource'])
	OR
	!isset($config['resources'][$params['resource']])
	OR
	!is_array($config['resources'][$params['resource']])
	OR
	!isset($config['resources'][$params['resource']]['controller'])
) {
	status_code(400, "", "Invalid resource!");
}

$params['controller'] = $config['resources'][$params['resource']]['controller'];

$methods = [
	"get" => "index",
	"post" => "create",
	"id-get" => "read",
	"id-put" => "update",
	"id-delete" => "delete",
	"put" => "bulkUpdate",
	"delete" => "bulkDelete",
];

$action = ($params['resource_id'] ? 'id-' : '') . $params['method'];
isset($methods[$action]) OR status_code(400, "", "Invalid resource action!");
$params['action'] = $methods[$action];

$controller_filename = $config['controllers_path'] . $params['controller'] . ".php";

if (empty($params['controller']) OR !file_exists($controller_filename)) {
	status_code(500, "", "The controller does not exists");
}

if (!include_once($controller_filename)) {
	status_code(500, "", "Can't have access to the controller");
}

if (!class_exists($params['controller'], false)) {
	status_code(500, "", "Controller class undefined");
}

if (!method_exists($params['controller'], $params['action'])) {
	status_code(500, "", "Controller action undefined");
}

$control = new $params['controller']($params);
$result = $control->{$params['action']}();

if (!is_null($result) && !headers_sent()) {
	ob_clean();
	doJSONResponse($result);
}