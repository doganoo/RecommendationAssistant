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
use OCP\IUser;

/**
 * Getter/Setter class for calculating the weighted recommendation
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class HybridItem {
	private $collaborative = 0;
	private $contentBased = 0;
	private $item = null;
	private $user = null;
	private $groupWeight = 1;

	/**
	 * sets the item for that the recommendation is made
	 *
	 * @param Item $item the item
	 * @since 1.0.0
	 */
	public function setItem(Item $item) {
		$this->item = $item;
	}

	/**
	 * sets the user for whom the recommendation is made
	 *
	 * @param IUser $user the user
	 * @since 1.0.0
	 */
	public function setUser(IUser $user) {
		$this->user = $user;
	}

	/**
	 * sets the group weight that is associated to the item and calculated
	 * from the user groups that is associated to the owner(s)
	 *
	 * @param float $groupWeight the weight
	 * @throws InvalidSimilarityValueException
	 * @since 1.0.0
	 */
	public function setGroupWeight(float $groupWeight) {
		$precision = 10;
		$compare = round($groupWeight, $precision, PHP_ROUND_HALF_EVEN);
		$one = round(1, $precision, PHP_ROUND_HALF_EVEN);
		$zero = round(0, $precision, PHP_ROUND_HALF_EVEN);
		//TODO is this a valid approach?
		if ($compare < $zero || $compare > $one) {
			throw new InvalidSimilarityValueException("the similarity value has to be between 0 and 1, $groupWeight given");
		}
		$this->groupWeight = $groupWeight;
	}

	/**
	 * sets the collaborative filtering similarity value for $item.
	 * the value has to be between 0 and 1, otherwise an exception
	 * of type InvalidSimilarityValueException is thrown.
	 *
	 * @param float $collaborative
	 * @throws InvalidSimilarityValueException
	 * @since 1.0.0
	 */
	public function setCollaborative(float $collaborative) {
		$precision = 10;
		$compare = round($collaborative, $precision, PHP_ROUND_HALF_EVEN);
		$one = round(1, $precision, PHP_ROUND_HALF_EVEN);
		$zero = round(0, $precision, PHP_ROUND_HALF_EVEN);
		//TODO is this a valid approach?
		if ($compare < $zero || $compare > $one) {
			throw new InvalidSimilarityValueException("the similarity value has to be between 0 and 1, " . (float)$collaborative);
		}
		$this->collaborative = $collaborative;
	}

	/**
	 * Returns the collaborative similarity value
	 *
	 * @return float
	 * @since 1.0.0
	 */
	public function getCollaborative(): float {
		return $this->collaborative;
	}

	/**
	 * returns the group weight
	 *
	 * @return float
	 * @since 1.0.0
	 */
	public function getGroupWeight(): float {
		return $this->groupWeight;
	}

	/**
	 * sets the content based similarity value for $item.
	 * the value has to be between 0 and 1, otherwise an exception
	 * of type InvalidSimilarityValueException is thrown.
	 *
	 * @param float $contentBased
	 * @throws InvalidSimilarityValueException
	 * @since 1.0.0
	 */
	public function setContentBased(float $contentBased) {
		$precision = 10;
		$compare = round($contentBased, $precision, PHP_ROUND_HALF_EVEN);
		$one = round(1, $precision, PHP_ROUND_HALF_EVEN);
		$zero = round(0, $precision, PHP_ROUND_HALF_EVEN);
		//TODO is this a valid approach?
		if ($compare < $zero || $compare > $one) {
			throw new InvalidSimilarityValueException("the similarity value has to be between 0 and 1, $contentBased given");
		}
		$this->contentBased = $contentBased;
	}

	/**
	 * Returns the content based similarity value
	 *
	 * @return float
	 * @since 1.0.0
	 */
	public function getContentBased(): float {
		return $this->contentBased;
	}

	/**
	 * returns a string representation of an instance of this class
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function __toString() {
		$val = HybridItem::weightedAverage($this);
		return "[#itemId#][#{$this->item->getId()}#][#itemName#][#{$this->item->getName()}#][#userId#][#{$this->user->getUID()}#][#collaborative#][#{$this->collaborative}#][#contentBased#][#{$this->contentBased}#][#groupWeight#][#{$this->groupWeight}#][#recommendation#][$val]";
	}

	/**
	 * calculates the weighted average of an HybridItem
	 *
	 * @param HybridItem $hybrid
	 * @return float
	 * @since 1.0.0
	 */
	public static function weightedAverage(HybridItem $hybrid): float {
		$contentBased = 0.26912;
		$collaborative = 1 - $contentBased;
		return $hybrid->getGroupWeight() * (($contentBased * $hybrid->getContentBased()) + ($collaborative * $hybrid->getCollaborative()));
	}
}
