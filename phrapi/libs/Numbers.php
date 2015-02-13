<?php defined("PHRAPI") or die("Direct access not allowed!");
/**
 * Class to extens Numbers data type
 *
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 * @final
 */
final class Numbers
{
	/**
	 * Receive a string|int|float value and return it in currency format
	 *
	 * Example:
	 *   $x = Numbers::currency(123456.789);
	 *
	 * @param string|int|float $number
	 * @param bool $return
	 * @return string
	 */
	static function currency($number, $return = true) {
		$number = (float) $number;
		$formated = number_format($number, 2, ".", ",");
		$formated = "$ {$formated}";

		if ($return) {
			return $formated;
		}

		echo $formated;
	}

	/**
	 * Receive a string|int|float value and return it in currency format
	 *
	 * Example:
	 *   $x = Numbers::currency(123456.789);
	 *
	 * @param string|int|float $number
	 * @param bool $return
	 * @return string
	 */
	static function currencyNoDecimals($number, $return = true) {
		$number = (float) $number;
		$formated = number_format($number, 0, ".", ",");
		$formated = "$ {$formated}";

		if ($return) {
			return $formated;
		}

		echo $formated;
	}
}