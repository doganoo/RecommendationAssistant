<?php
/**
 * @copyright Copyright (c) 2017, Dogan Ucar (dogan@dogan-ucar.de)
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\RecommendationAssistant\Util;

use OCA\RecommendationAssistant\Exception\InvalidSimilarityValueException;
use OCA\RecommendationAssistant\Interfaces\IContentReader;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Log\Logger;
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Objects\Similarity;
use OCP\IUser;


/**
 * Utility class for helper methods that are not specific.
 * This class is not instantiable because all methods are static.
 *
 * @package OCA\RecommendationAssistant\Util
 * @since 1.0.0
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
	 * compares the IDs of $user1 and $user2. The comparision is case sensitive!
	 *
	 * @param IUser $user1
	 * @param IUser $user2
	 * @return bool
	 * @since 1.0.0
	 */
	public static function sameUser(IUser $user1, IUser $user2): bool {
		return strcmp($user1->getUID(), $user2->getUID()) === 0;
	}


	/**
	 * creates an instance of Similarity and returns it. The method catches
	 * the InvalidSimilarityException and returns an empty Similarity instance
	 * if the exception is thrown.
	 *
	 * @param int $value
	 * @param int $status
	 * @param string $description
	 * @return Similarity
	 * @since 1.0.0
	 */
	public static function createSimilarity(float $value, int $status, string $description): Similarity {
		$similarity = new Similarity();
		try {
			$similarity->setValue($value);
			$similarity->setStatus($status);
			$similarity->setDescription($description);
		} catch (InvalidSimilarityValueException $exception) {
			Logger::error($exception->getMessage());
		}
		return $similarity;
	}

	/**
	 * This method uses PHP class reflection in order to get the specified mime types
	 * in OCA\RecommendationAssistant\Interfaces\IContentReader. The method returns
	 * true if the given mime type corresponds to one of those defined in IContentReader.
	 *
	 * @param string $mimeType
	 * @return bool
	 * @since 1.0.0
	 */
	public static function validMimetype(string $mimeType): bool {
		$mimeType = strtolower($mimeType);
		try {
			$class = new \ReflectionClass(IContentReader::class);
			$constants = $class->getConstants();
			array_walk($constants, function (&$value, $key) {
				$value = strtolower($value);
			});
			return in_array($mimeType, $constants);
		} catch (\ReflectionException $exception) {
			ConsoleLogger::error($exception->getMessage());
			Logger::error($exception->getMessage());
			return false;
		}
	}
}