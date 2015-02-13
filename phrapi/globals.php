<?php
/**
 * PHP Raccoon Framework for APIs by Tecnologias Web de Mexico S.A. de C.V.
 *
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 */

$_flags = 1;

define("PHRAPI", ++$_flags);

define("PHRAPI_NAME", "PHRAPI by wt.com.mx");

define("DS", DIRECTORY_SEPARATOR);

header("X-Framework: " . PHRAPI_NAME);

function stopwatch() {
	if (!isset($GLOBALS['internal_stopwatch_start'])) {
		$GLOBALS['internal_stopwatch_start'] = microtime(true);
	}
	$start = $GLOBALS['internal_stopwatch_start'];
	$actual = microtime(true);
	$lapsed = $actual - $start;

	$hrs = "00";
	$min = "00";
	$sec = "00";
	$mic = 0;

	$hrs = floor($lapsed / 3600);
	$lapsed -= 3600 * $hrs;
	$min = floor($lapsed / 60);
	$lapsed -= 60 * $min;
	$sec = floor($lapsed);
	$mic = substr(strrchr($lapsed, "."), 1);

	if ($hrs < 10) {
		$hrs = "0" . $hrs;
	}
	if ($min < 10) {
		$min = "0" . $min;
	}
	if ($sec < 10) {
		$sec = "0" . $sec;
	}

	Console("Stopwatch: {$hrs}:{$min}:{$sec}.{$mic}");
}

/**
 * Carga librerías en el entorno de ejecución, puede estar almacenadas en /libs/ o en /rapi/libs/
 *
 * Referencia: PHP 5 Objects, Patterns, and Practice, Chapter 5, PHP and Packages
 *
 * @param string Nombre de la clase a cargar
 * @return void
 */
spl_autoload_register(function($classname)
{
	$paths = [
		"phrapi_libs",
		"libs",
	];

	$loaded = false;
	$class_path = "";

	foreach($paths as $path) {
		$class_path = $path . DS . $classname;
		$class_path = preg_replace('/\_/', DS, $class_path);
		$class_path .= ".php";
		if (file_exists($class_path) && is_readable($class_path)) {
			require_once $class_path;
			$loaded = true;
		}
	}
});

/**
 * http://www.restapitutorial.com/httpstatuscodes.html
 * @param number $code
 */
function status_code($code = 500, $message = "", $more_info = "") {
	$suppress_response_codes =
		(
			isset($_GET['suppress_response_codes'])
			&&
			$_GET['suppress_response_codes'] === 'true'
		) ? true : false;

	$code = (int) $code;
	$codes = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing (WebDAV)',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Not-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status (WebDAV)',
		208 => 'Already Reported (WebDAV)',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'User Proxy',
		306 => 'Unused',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect (experimental)',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout ',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'Im a teapot (RFC 2324)',
		420 => 'Enhance Your Calm (Twitter)',
		422 => 'Unprocessable Entity (WebDAV)',
		423 => 'Locked (WebDAV)',
		424 => 'Failed Dependency (WebDAV)',
		425 => 'Reserved for WebDAV',
		426 => 'Upgrade Required',
		428 => 'Precondition Required (draft)',
		429 => 'Too Many Requests (draft)',
		431 => 'Request Header Fields Too Large (draft)',
		444 => 'No Response (Nginx)',
		449 => 'Retry With (Microsoft)',
		450 => 'Blocked by Windows Parental Controls (Microsoft)',
		499 => 'Client Closed Request (Nginx)',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates (Experimental)',
		507 => 'Insufficient Storage (WebDAV)',
		508 => 'Loop Detected (WebDAV)',
		509 => 'Bandwidth Limit Exceeded (Apache)',
		510 => 'Not Extended',
		511 => 'Network Authentication Required (draft)',
		598 => 'Network read timeout error',
		599 => 'Network connect timeout error',
	];

	if (!array_key_exists($code, $codes) && empty($message)) {
		$code = 500;
		$message = "Undefined";
	}

	if (empty($message)) {
		$message = $codes[$code];
	}

	if ($suppress_response_codes) {
		doJSONResponse([
			"response_code" => $code,
			"message" => $message,
			"more_info" => $more_info
		]);
	} else {
		http_response_code($code);
		//header("HTTP/1.0 {$code} {$codes[$code]}");
		header('Access-Control-Allow-Origin: *');
		header("Content-type: application/json");
		header("Status: {$code} {$message}");
	}

	if ($code >= 400) {
		die;
	}
}

/**
 * Crea una respuesta, devolviendo un objeto en JSON, se puede hacer un callback a una funcion JS
 *
 * @param int $status
 * @param string $message
 * @param mixed $data
 */
function doJSONResponse($data = []) {
	$json = json_encode($data,  JSON_PRETTY_PRINT);

	$callback = getValueFrom($_GET, 'callback');
	if (!empty($callback)) {
		header('Access-Control-Allow-Origin: *');
		header("Content-type: application/javascript");
		echo "{$callback}({$json});";
	} else {
		header('Access-Control-Allow-Origin: *');
		header("Content-type: application/json");
		echo $json;
	}

	exit;
}

/**
 * Muestra un mensaje (debug)
 *
 * @param mixed $data
 * @param string $type (normal|error)
 */
function Console($data) {
	$data = print_r($data, true);
	$backtrace = "";
	foreach(debug_backtrace() as $_trace) {
		$backtrace[] = basename($_trace['file']) . ":" . $_trace['line'];
	}
	$backtrace = join(" < ", $backtrace);
	$data = $backtrace . "\n" . $data;
	print($data);
	flush();
}
/**
 * Abreviacion de Console
 * @param mixed $data
 */
function D($data) {
	Console($data);
}
/**
 * Abreviacion de Console pero para errores
 * @param mixed $data
 */
function F($data) {
	Console($data);
	echo PHP_EOL . "EOB";
	exit;
}

/**
 * Obtiene un valor de un arreglo (simple o multidimencional) o un objeto (simple o compuesto)
 *
 * Ejemplo:
 * <code>
 *   $id = getValueFrom($_POST, "id", 0);
 *
 *   $x = getValueFrom(["a" => ["b" => ["c" => 123]]], "a.b.c");
 * </code>
 *
 * @todo make a xpath implementation
 * @return mixed
 * @param array $data
 * @param string $path
 * @param mixed $default[optional]
 */
function getValueFrom($data, $path = null, $default = null, $sanitize = null, $callback = null)
{
	$beforeReturn = function($value) use ($sanitize, $callback) {
		$value = Sanitize::by($value, $sanitize);
		if (is_callable($callback)) {
			$value = $callback($value);
		}
		return $value;
	};

	if (empty($path) || $path == null) {
		return $beforeReturn($default);
		//return Sanitize::by($default, $sanitize);
	}

	if (!is_array($data) && !is_object($data)) {
		return $beforeReturn($default);
		//return Sanitize::by($default, $sanitize);
	}

	// without a path
	if (strpos($path, ".") === false) {
		if (is_array($data) && isset($data[$path]))
			return $beforeReturn($data[$path]);
			//return Sanitize::by($data[$path], $sanitize);

		if (is_object($data) && isset($data->$path))
			return $beforeReturn($data->$path);
			//return Sanitize::by($data->$path, $sanitize);

		return $beforeReturn($default);
		//return Sanitize::by($default, $sanitize);
	}

	// with a path
	$value = $data;
	foreach(explode(".", $path) as $crumb) {
		if (is_array($value) && isset($value[$crumb])) {
			$value = $value[$crumb];
		}
		elseif (is_object($value) && isset($value->$crumb)) {
			$value = $value->$crumb;
		} else {
			Sanitize::by($default, $sanitize);
		}
	}

	return $beforeReturn($value);
	//return Sanitize::by($value, $sanitize);
}
function getArrayFrom($data, $path) {
	return getValueFrom($data, $path, [], FILTER_SANITIZE_PHRAPI_ARRAY);
}
function getIntFrom($data, $path, $default = 0, $callback = null) {
	return (int) getValueFrom($data, $path, $default, FILTER_SANITIZE_PHRAPI_INT, $callback);
}
function getFloatFrom($data, $path, $default = 0, $callback = null) {
	return (float) getValueFrom($data, $path, $default, FILTER_SANITIZE_PHRAPI_FLOAT, $callback);
}
function getStringFrom($data, $path, $default = "", $callback = null) {
	return getValueFrom($data, $path, $default, FILTER_SANITIZE_STRING, $callback);
}

/**
 * Regresa un hash, se puede configurar pasando un arreglo como argumento.
 *
 * Ejemplo:
 * <code>
 * $hash = getHash($_POST, [
 *   "arg1" => FILTER_SANITIZE_STRING,
 *   "arg2" => FILTER_SANITIZE_STRING
 * ]);
 * $hash = getHash($_POST, [
 *   [
 *     "name" => "id",
 *     "default" => 0,
 *     "type" => "int"
 *   ],
 *   [
 *     "name" => "name",
 *     "default" => "",
 *     "sanitize" => FILTER_SANITIZE_STRING
 *   ]
 * ));
 * </code>
 *
 * @param array $from [$_POST, $_GET, $_REQUEST, $_SERVER, $_SESSION, etc]
 * @param array $config [[name=>string, default=>mixed, type=[string, integer, float, boolean], sanitize=>FILTER_SANITIZE_]]
 * </p>
 */
function getHash($from = null, $config = null) {
	// argumentos invalidos?
	if (!is_array($from) || !is_array($config)) {
		return null;
	}

	$data = [];
	foreach($config as $param_key => $param) {
		$_data = null;

		// se intenta accedor al valor por <nombre>:<sanitizacion>
		if (is_string($param_key) && !is_array($param) && !is_object($param)) {
			$data[$param_key] = getValueFrom($from, $param_key, null, $param);
		}

		// se intenta acceder al valor esquema en arreglo
		if (is_integer($param_key) && is_array($param)) {
			$name = getValueFrom($param, 'name', '');
			$default = getValueFrom($param, 'default', '');
			$sanitize = getValueFrom($param, 'sanitize', FILTER_DEFAULT);
			$type = getValueFrom($param, 'type', 'string');
			if (!empty($name)) {
				$_data = getValueFrom($from, $name, $default, $sanitize);
			}

			if (!empty($type) && is_string($type)) {
				settype($_data, $type);
			}

			$data[$name] = $_data;
		}
	}

	return $data;
}

/**
 * Obtiene un recurso remoto,
 *
 * @param array $config [url=string, method=get|post, return=raw|json, args=hash]
 */
function getRemote($config) {
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16 Safari/535.7";
	if (is_string($config)) {
		$config = (object) [
			'url' => $config,
			'method' => 'get',
			'return' => 'raw',
			'cookie' => '',
			'cookie_file' => '',
			'cookie_jar' => '',
			'user_agent' => $user_agent,
			'referer' => '',
			'debug' => false,
			'save' => false,
			'args' => []
		];
	} else {
		$config = (object) ($config + [
			'url' => '',
			'method' => 'get',
			'return' => 'raw',
			'cookie' => '',
			'cookie_file' => '',
			'cookie_jar' => '',
			'user_agent' => $user_agent,
			'referer' => '',
			'debug' => false,
			'save' => false,
			'args' => []
		]);
	}

	if (empty($config->url)) {
		return false;
	}

	$config->original_url = $config->url;

	if (sizeof($config->args) > 0 && $config->method == 'get') {
		$args = [];
		foreach($config->args as $arg_name => $arg_value) {
			$args[] = "{$arg_name}=" . urlencode($arg_value);
		}
		$config->url .= "?" . join("&", $args);
	}

	if ($config->debug) {
		D($config);
	}

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $config->url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_USERAGENT, $config->user_agent);
	if (!empty($config->referer)) {
		curl_setopt($curl, CURLOPT_REFERER, $config->referer);
	}
	if (preg_match('/$https/', $config->url)) {
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	}
	if (!empty($config->cookie)) {
		curl_setopt($curl, CURLOPT_COOKIE, $config->cookie);
	}
	if (!empty($config->cookie_file)) {
		curl_setopt($curl, CURLOPT_COOKIEFILE, $config->cookie_file);
	}
	if (!empty($config->cookie_jar)) {
		curl_setopt($curl, CURLOPT_COOKIEJAR, $config->cookie_jar);
	}
	if (sizeof($config->args) > 0 && $config->method == 'post') {
		curl_setopt($curl, CURLOPT_POSTFIELDS, $config->args);
	}
	$response = curl_exec($curl);
	curl_close($curl);

	if (!empty($config->save)) {
		if ((file_exists($config->save) && is_writable($config->save)) OR is_writable(dirname($config->save))) {
			if (file_put_contents($config->save, $response)) {
				if ($config->debug) {
					D("Se guardó '{$config->save}'");
				}
			} else {
				if ($config->debug) {
					D("Error al guardar '{$config->save}'");
				}
			}
		}
	}

	if ($config->return == "json") {
		return json_decode($response);
	}

	return $response;
}

/**
 * Obtiene un JSON remoto
 *
 * @param array $config [url=string, method=get|post, args=hash]
 */
function getRemoteJSON($config = []) {
	if (is_string($config)) {
		$config = [
			'url' => $config,
			'method' => 'get',
			'return' => 'json',
			'args' => []
		];
	} else {
		$config = $config + [
			'url' => '',
			'method' => 'get',
			'return' => 'json',
			'args' => []
		];
	}

	return getRemote($config);
}

/**
* Get the user IP
*
* @uses $control->getUserIP();
*
* @return string The user IP
*/
function getUserIP() {
	if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
		$ip = getenv ( "HTTP_CLIENT_IP" );
	elseif (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
		$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	elseif (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
		$ip = getenv ( "REMOTE_ADDR" );
	elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
		$ip = $_SERVER ['REMOTE_ADDR'];
	else
		$ip = "0.0.0.0";

	return ($ip);
}

/*
 * Sort an array by the key length
 *
 * http://stackoverflow.com/questions/3955536/php-sort-hash-array-by-key-length
 */
function sortByLengthReverse($a, $b){
    return strlen($b) - strlen($a);
}