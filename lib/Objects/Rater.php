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

namespace OCA\RecommendationAssistant\Objects;


use OCA\RecommendationAssistant\Exception\InvalidRatingException;
use OCP\IUser;

/**
 * Getter/Setter class that represents a item rater
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class Rater {
	/**
	 * @var IUser $user
	 */
	private $user;

	/**
	 * @var int $rating
	 */
	private $rating = self::NO_ACCESS;

	/**
	 * @const int NO_RATING
	 */
	const NO_RATING = 0;

	/**
	 * @const int NO_ACCESS
	 */
	const NO_ACCESS = -1;

	/**
	 * Class constructor gets an user injected
	 *
	 * @param $user
	 * @since 1.0.0
	 */
	public function __construct($user = null) {
		$this->user = $user;
	}

	/**
	 * Returns the user that is responsible for the rating
	 *
	 * @return IUser $user
	 * @since 1.0.0
	 */
	public function getUser(): IUser {
		return $this->user;
	}

	/**
	 * Returns the rating of an user
	 *
	 * @return int $rating
	 * @since 1.0.0
	 */
	public function getRating(): int {
		return $this->rating;
	}

	/**
	 * sets the rating of an user
	 *
	 * @param int $rating the rating of the user
	 * @throws InvalidRatingException invalid rating
	 * @since 1.0.0
	 */
	public function setRating(int $rating) {
		//TODO define upper and lower limits and do not hardcode!
		if ($rating >= 0 || $rating <= 5) {
			$this->rating = $rating;
		} else {
			throw new InvalidRatingException("$rating is not a valid rating");
		}

	}

	/**
	 * determines whether the instance is valid or not.
	 * The instance has to contain a user object in order to be valid.
	 *
	 * @return bool
	 */
	public function isValid(): bool {
		return $this->user !== null;
	}


}