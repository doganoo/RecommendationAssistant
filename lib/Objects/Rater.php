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
	private $rating;

	/**
	 * @const int LIKE
	 */
	public const LIKE = 1;

	/**
	 * @const int NO_LIKE
	 */
	public const NO_LIKE = 0;

	/**
	 * Class constructor gets an user injected
	 *
	 * @since 1.0.0
	 */
	public function __construct($user) {
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
	 * @since 1.0.0
	 */
	public function setRating(int $rating) {
		$this->rating = $rating;
	}


}