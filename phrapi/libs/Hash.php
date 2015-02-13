<?php defined("PHRAPI") or die("Direct access not allowed!");

/**
 * Class to hash and Id using base36 and a random string
 *
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 * @final
 */
final class Hash
{
	static function encode($id, $length = 20, $separator = "x") {
		if (!is_int($id) || $id <= 0)
			return "";

		$hash = $base36 = base_convert($id, 10, 36);
		if (strlen($hash) < $length)
		{
			$random = str_shuffle('abcdefghijklmnopqrstuvwyz0123456789');
			$hash = $base36 . $separator . substr($random, 0, $length - (strlen($base36) + 1));
		} else {
			$hash .= $separator;
		}

		return strtoupper($hash);
	}

	static function decode($string, $separator = "x") {
		$position = strripos($string, $separator);
		$base36 = $string;
		if ($position >= 0)
			$base36 = substr($string, 0, strripos($string, $separator));
		return base_convert($base36, 36, 10);
	}
}