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

namespace OCA\RecommendationAssistant\Objects;

use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;
use OCA\RecommendationAssistant\Exception\InvalidRatingException;

/**
 * Getter/Setter class that represents a item rater
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since   1.0.0
 */
class Rater implements IComparable{
	/**
	 * @const RATING_UPPER_LIMIT the upper limit of an rating
	 */
	const RATING_UPPER_LIMIT = 5;
	/**
	 * @const RATING_LOWER_LIMIT the lower limit of an rating
	 */
	const RATING_LOWER_LIMIT = 0;
	/**
	 * @const int NO_RATING
	 */
	const NO_RATING = 0;
	/**
	 * @const int NO_ACCESS
	 */
	const NO_ACCESS = - 1;
	/**
	 * @var string $uid
	 */
	private $uid;
	/**
	 * @var int $rating
	 */
	private $rating = self::NO_ACCESS;

	/**
	 * Class constructor gets an user injected
	 *
	 * @param string $uid
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $uid = null){
		$this->uid = $uid;
	}

	/**
	 * determines whether the instance is valid or not.
	 * The instance has to contain a user object in order to be valid.
	 *
	 * @return bool
	 */
	public function isValid(): bool{
		return $this->uid !== null && $this->uid !== "";
	}

	public function __toString(){
		return $this->uid;
	}

	/**
	 * Returns the rating of an user
	 *
	 * @return float $rating
	 * @since 1.0.0
	 */
	public function getRating(): float{
		return $this->rating;
	}

	/**
	 * sets the rating of an user
	 *
	 * @param float $rating the rating of the user
	 *
	 * @throws InvalidRatingException invalid rating
	 * @since 1.0.0
	 */
	public function setRating(float $rating){
		if($rating >= self::RATING_LOWER_LIMIT || $rating <= self::RATING_UPPER_LIMIT){
			$this->rating = $rating;
		} else{
			throw new InvalidRatingException("$rating is not a valid rating");
		}
	}

	/**
	 * @param $object
	 *
	 * @return int
	 */
	public function compareTo($object): int{
		if($object instanceof Rater){
			if($this->getUserId() === $object->getUserId()) return 0;
			if($this->getUserId() > $object->getUserId()) return 1;
			if($this->getUserId() < $object->getUserId()) return - 1;
		}
		return - 1;
	}

	/**
	 * Returns the user that is responsible for the rating
	 *
	 * @return string $uid
	 * @since 1.0.0
	 */
	public function getUserId(): string{
		return $this->uid;
	}
}