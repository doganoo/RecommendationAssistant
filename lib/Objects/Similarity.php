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


use OCA\RecommendationAssistant\Exception\InvalidSimilarityValueException;

/**
 * Getter/Setter class that represents the similarity
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class Similarity {
	/**
	 *
	 * @var float $value the similarity value
	 */
	private $value = 0.0;

	/**
	 *
	 * @var array the similarity status
	 */
	private $status = 0;

	/**
	 *
	 * @var string $description status description
	 */
	private $description = null;

	/**
	 *
	 * @const int VALID status valid
	 */
	const VALID = 0;

	/**
	 *
	 * @const int NO_SIMILARITY_AVAILABLE no similarity possible
	 */
	const NO_SIMILARITY_AVAILABLE = 1;

	/**
	 *
	 * @const int NO_COSINE_SQUARE_POSSIBLE root square not possible
	 */
	const NO_COSINE_SQUARE_POSSIBLE = 4;

	/**
	 *
	 * @const int SAME_COSINE_ITEMS items are the same
	 */
	const SAME_COSINE_ITEMS = 5;

	/**
	 *
	 * @const int NOT_ENOUGH_ITEMS_IN_ITEM_BASE not enough items in item base
	 */
	const NOT_ENOUGH_ITEMS_IN_ITEM_BASE = 6;

	/**
	 * returns the similarity value
	 *
	 * @return float the similarity value
	 * @since 1.0.0
	 */
	public function getValue(): float {
		return $this->value;
	}

	/**
	 * sets the similarity value.
	 * The value must be 0 <= $value <= 1, otherwise an InvalidSimilarityException
	 * is thrown.
	 *
	 * @param float $value
	 * @throws InvalidSimilarityValueException
	 * @since 1.0.0
	 */
	public function setValue(float $value) {
		$precision = 10;
		$compare = round($value, $precision, PHP_ROUND_HALF_EVEN);
		$five = round(Rater::RATING_UPPER_LIMIT, $precision, PHP_ROUND_HALF_EVEN);
		$zero = round(Rater::RATING_LOWER_LIMIT, $precision, PHP_ROUND_HALF_EVEN);
		if ($compare >= $zero || $compare <= $five) {
			$this->value = $value;
		} else {
			throw new InvalidSimilarityValueException("the similarity value has to be between 0 and 5, $value given");
		}

	}

	/**
	 * returns the similarity status. Possible status:
	 *
	 * <ul>VALID the similarity is valid</ul>
	 * <ul>NO_SIMILARITY_AVAILABLE  the similarity could not be measured</ul>
	 * <ul>NO_OVERLAPPING_KEYWORDS no overlapping keywords found</ul>
	 * <ul>ITEM_OR_USER_PROFILE_EMPTY no keywords in the item or user profile found</ul>
	 * <ul>NO_COSINE_SQUARE_POSSIBLE cosine similarity could not be measured</ul>
	 * <ul>SAME_COSINE_ITEMS the items compared are the same</ul>
	 * <ul>NOT_ENOUGH_ITEMS_IN_ITEM_BASE not enough items in item base available</ul>
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * sets the similarity status. Possible status:
	 *
	 * <ul>NO_SIMILARITY_AVAILABLE  the similarity could not be measured</ul>
	 * <ul>NO_OVERLAPPING_KEYWORDS no overlapping keywords found</ul>
	 * <ul>ITEM_OR_USER_PROFILE_EMPTY no keywords in the item or user profile found</ul>
	 * <ul>NO_COSINE_SQUARE_POSSIBLE cosine similarity could not be measured</ul>
	 * <ul>SAME_COSINE_ITEMS the items compared are the same</ul>
	 * <ul>NOT_ENOUGH_ITEMS_IN_ITEM_BASE not enough items in item base available</ul>
	 *
	 *
	 * @param null $status
	 * @since 1.0.0
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * returns the status description
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * sets the status description
	 *
	 * @param string $description
	 * @since 1.0.0
	 */
	public function setDescription(string $description) {
		$this->description = $description;
	}

	/**
	 * checks if this similarity instance is valid or not. The similarity
	 * has to be in one of the following status:
	 *
	 * <ul>VALID</ul>
	 * <ul>SAME_COSINE_ITEMS</ul>
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function isValid(): bool {
		return $this->getStatus() === Similarity::VALID || $this->getStatus() === Similarity::SAME_COSINE_ITEMS;
	}


}