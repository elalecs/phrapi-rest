<?php defined("PHRAPI") or die("Direct access not allowed!");

/**
* Flag for sanitize a string to use in mysql
*/
define("FILTER_SANITIZE_PHRAPI_MYSQL", PHRAPI << ++$GLOBALS['_flags']);

/**
* Flag for sanitize a string to use in mysql
*/
define("FILTER_SANITIZE_PHRAPI_INT", PHRAPI << ++$GLOBALS['_flags']);

/**
 * Flag for sanitize a normal string
 */
define("FILTER_SANITIZE_PHRAPI_FLOAT", PHRAPI << ++$GLOBALS['_flags']);

/**
 * Flag for sanitize an array
 */
define("FILTER_SANITIZE_PHRAPI_ARRAY", PHRAPI << ++$GLOBALS['_flags']);

/**
 * Class for sanitize
 *
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 * @final
 */
final class Sanitize {
	static function by($mixed, $type = false) {
		if (!$type OR !is_numeric($type) OR empty($type) OR $type === null)
		{
			$type = FILTER_DEFAULT;
		}

		if ($type === FILTER_SANITIZE_PHRAPI_MYSQL) {
			return Sanitize::mysql($mixed);
		}

		if ($type === FILTER_SANITIZE_PHRAPI_INT) {
			return Sanitize::int($mixed);
		}

		if ($type === FILTER_SANITIZE_PHRAPI_FLOAT) {
			return Sanitize::float($mixed);
		}

		if ($type === FILTER_SANITIZE_PHRAPI_ARRAY) {
			return Sanitize::array_type($mixed);
		}

		return filter_var($mixed, $type);
	}

	static function email($mixed) {
		return filter_var($mixed, FILTER_SANITIZE_EMAIL);
	}

	static function int($mixed) {
		return (int) filter_var($mixed, FILTER_SANITIZE_NUMBER_INT);
	}

	static function float($mixed) {
		return (float) filter_var($mixed, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}

	static function url($mixed) {
		return filter_var($mixed, FILTER_SANITIZE_URL);
	}

	static function string($mixed) {
		return filter_var($mixed, FILTER_SANITIZE_STRING);
	}

	static function webstring($mixed) {
		$mixed = filter_var($mixed, FILTER_SANITIZE_MAGIC_QUOTES);
		$mixed = strip_tags($mixed);

		return $mixed;
	}

	static function mysql($mixed) {
		$mixed = mysql_escape_mimic($mixed);

		return $mixed;
	}

	static function array_type($mixed) {
		return (array) $mixed;
	}
}