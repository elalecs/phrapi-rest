<?php
/**
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 */
$errors = array();
if (floatval(phpversion()) < 5.4)
	$errors[] = "PHP 5.4+, actual: " . floatval(phpversion());
if (!function_exists("json_decode"))
	$errors[] = "JSON extension for PHP";
if (!function_exists("mysql_connect"))
	$errors[] = "MySQL extension for PHP";
if (!function_exists("simplexml_load_file"))
	$errors[] = "SimpleXML extension for PHP";

if (count($errors) > 0)
{
	$error_message = "The app can't run, need all this first:<br>\n";
	$error_message .= implode("<br/>\n", $errors);
	$error_message .= "<br>\n<br>\nPlease call to your software provider.";
	die($error_message);
}

/**
 * Validaton for possible exploit
 */
if (sizeof($_REQUEST) > 500)
	die("possible exploit");

/**
 * Server overloaded? take a breath
 */
$load = sys_getloadavg();
if ($load[0] > 80) {
	header('HTTP/1.1 503 Too busy, try again later');
	die('Server too busy. Please try again later.');
}