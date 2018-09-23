<?php
/**
 * @copyright Copyright (c) 2017, Dogan Ucar (dogan@dogan-ucar.de)
 * @license   GNU AGPL version 3 or any later version
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\RecommendationAssistant\Util;
/**
 * NumberUtil is a utility class for all numbers (float, double, int). It defines
 * all helper methods that are relevant for numbers. This class is not instantiable
 * because all methods are static.
 *
 * @package OCA\RecommendationAssistant\Util
 * @since   1.0.0
 * @deprecated
 */
class NumberUtil{
	private function __construct(){
	}

	/**
	 * This method checks if $value is greater than $value1. If $gte is set to
	 * true, the method checks if $value is greater than or equal to $value1.
	 * From http://php.net/manual/de/language.types.float.php:
	 * So never trust floating number results to the last digit, and do not
	 * compare floating point numbers directly for equality.
	 * Contributed notes in http://php.net/manual/de/language.types.float.php
	 * suggests rounding the values before comparing (see 115 catalin dot luntraru at gmail dot com).
	 *
	 * @param float $value
	 * @param float $value1
	 * @param bool  $gte
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function floatGreaterThan(float $value, float $value1, bool $gte = false){
		$value = round($value, 10, PHP_ROUND_HALF_EVEN);
		$value1 = round($value1, 10, PHP_ROUND_HALF_EVEN);
		if($gte){
			return $value >= $value1;
		} else{
			return $value > $value1;
		}
	}

	/**
	 * This method compares two float numbers for equality. PHP float values
	 * should never be compared directly, according to: http://php.net/manual/de/language.types.float.php
	 *
	 * @param float $value
	 * @param float $value1
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function compareFloat(float $value, float $value1){
		$epsilon = 0.00001;
		if(abs($value - $value1) < $epsilon){
			return true;
		}
		return false;
	}
}