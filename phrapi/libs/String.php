<?php defined("PHRAPI") or die("Direct access not allowed!");
/**
 * Class to extens String data type
 *
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 * @final
 */
final class String
{
	public static $exceptions = array("la", "las", "el", "los", "y", "al", "se", "me", "a","ante","bajo","con","contra","de","del","desde","en","hacia","hasta","para","por","según","sin","sobre","tras");

	/**
	 * Receive a special string like "My name is :name" an replaces the tokens
	 * for values
	 *
	 * Example:
	 *   $x = String::formated("My name is :name", array("name" => "Pedro"));
	 *
	 * @param string $srings
	 * @param array $replacements
	 * @return string
	 */
	static function formated($s, $p) {
		if (!is_array($p) && !is_object($p))
			return $s;

		if (is_array($p)) {
			uksort($p, "sortByLengthReverse");
		}

		foreach ($p as $k => $v) {
			if (is_numeric($k)) {
				D("Error at String::formated. " . print_r($s, 1) . print_r($p, 1), 1);
			}
			$k = (string) $k;
			$v = (string) $v;
			$s = preg_replace("/\:{$k}/", $v, $s);
		}

		return $s;
	}

	/**
	 * Convert a string into someone in Title Case
	 * @param string $string
	 * @return string
	 */
	static function toTitleCase($string, $except_prepositions = true) {
		if (function_exists("mb_convert_case")) {
			$string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");;
		} else {
			$string = ucwords(strtolower($string));
		}

		if ($except_prepositions) {
			$exceptions = String::$exceptions;
			foreach ($exceptions as $except) {
				$string = preg_replace("/(\s+)({$except})/i", '$1'.$except, $string);
			}
		}

		return $string;
	}

	/**
	 * Escape a string to be used into a SEO link
	 *
	 * Example:
	 *   'arbol-ninos-y-pinguinos' = $this->toSeoStyle('árbol, niños y pigüinos');
	 *
	 * @param $str
	 * @return string
	 */
	static function toSeoStyle($str, $allow_spaces = true) {
		$str = preg_replace('/\t|\n/', '-', $str);
		$str = preg_replace('/[\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]/', '', $str);
		$str = preg_replace('/\xC2[\xA1-\xBF]|\xC3[\x86-\x87]|\xC3\x90|\xC3\x97|\xC3[\x9E-\x9F]|\xC3[\xA6-\xA7]|\xC3\xBE/', '', $str);
		$str = preg_replace('/\xC3[\x80-\x85]/', 'A', $str);
		$str = preg_replace('/\xC3[\x88-\x8B]/', 'E', $str);
		$str = preg_replace('/\xC3[\x8C-\x8F]/', 'I', $str);
		$str = preg_replace('/\xC3[\x92-\x96]|\xC3\x98/', 'O', $str);
		$str = preg_replace('/\xC3[\x99-\x9C]/', 'U', $str);
		$str = preg_replace('/\xC3\x91/', 'N', $str);
		$str = preg_replace('/\xC3\x9D/', 'Y', $str);
		$str = preg_replace('/\xC3[\xA0-\xA5]/', 'a', $str);
		$str = preg_replace('/\xC3[\xA8-\xAB]/', 'e', $str);
		$str = preg_replace('/\xC3[\xAC-\xAF]/', 'i', $str);
		$str = preg_replace('/\xC3[\xB2-\xB6]|\xC3\xB8/', 'o', $str);
		$str = preg_replace('/\xC3[\xB9-\xBC]/', 'u', $str);
		$str = preg_replace('/\xC3\xB1/', 'n', $str);
		$str = preg_replace('/\xC3\xBD|\xC3\xBF/', 'y', $str);
		$str = trim($str);
		$str = strtolower($str);

		if ($allow_spaces)
			$str = preg_replace('/\ /', '-', $str);
		else
			$str = preg_replace('/\ /', '', $str);

		return $str;
	}
}
