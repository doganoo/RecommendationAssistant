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
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Log\Logger;
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

	public static function setErrorHandler(bool $default = false) {
		if ($default) {
			\restore_error_handler();
			return;
		}
		set_error_handler(function ($errno, $errstr, $errfile, $errline) {
			$jsonString = \json_encode([$errno, $errstr, $errfile, $errline]);
			switch ($errno) {
				case \E_ERROR:
					Logger::error($jsonString);
					ConsoleLogger::error($jsonString);
					break;
				case \E_WARNING:
					Logger::warn($jsonString);
					ConsoleLogger::warn($jsonString);
					break;
				default:
					Logger::debug($jsonString);
					ConsoleLogger::debug($jsonString);
					break;
			}
		});
	}

	public static function isFile($path): bool {
		return \is_file($path) === true;
	}
}