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

use OCA\RecommendationAssistant\Objects\Rater;

/**
 * Utility class for helper methods that are not specific.
 * This class is not instantiable because all methods are static.
 *
 * @package OCA\RecommendationAssistant\Util
 * @since   1.0.0
 */
class Util {
	/**
	 * class constructor is private because all methods are public static.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
	}

	/**
	 * @param string $uid
	 * @param float $rating
	 *
	 * @return Rater
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	public static function toRater(string $uid, float $rating): Rater {
		$rater = new Rater($uid);
		$rater->setRating($rating);
		return $rater;
	}

	public static function toRating(int $unixTimestamp): int {
		$then = new \DateTime();
		$then->setTimestamp($unixTimestamp);

		$now = new \DateTime();

		$diff = $then->diff($now)->days;
		if ($diff <= 15) return 5;
		if ($diff > 15 && $diff <= 30) return 4;
		if ($diff > 30 && $diff <= 45) return 3;
		if ($diff > 45 && $diff <= 60) return 2;
		if ($diff > 60 && $diff <= 75) return 1;
		if ($diff > 75) return 0;
	}
}