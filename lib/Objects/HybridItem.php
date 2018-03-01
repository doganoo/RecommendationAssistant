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


use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Exception\InvalidSimilarityValueException;
use OCP\IUser;

/**
 * Getter/Setter class for calculating the weighted recommendation
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class HybridItem {
	/**
	 * @var Similarity $collaborative the collaborative similarity
	 */
	private $collaborative = null;

	/**
	 * @var Similarity $contentBased the content based similarity
	 */
	private $contentBased = null;

	/**
	 * @var Item $item the item
	 */
	private $item = null;

	/**
	 * @var IUser $user the user
	 */
	private $user = null;

	/**
	 * @var float $groupWeight the group weights
	 */
	private $groupWeight = 1;

	/**
	 * @const TRANSPARENCY_BOTH transparency both
	 */
	const TRANSPARENCY_BOTH = 0;

	/**
	 * @const TRANSPARENCY_COLLABORATIVE transparency collaborative
	 */
	const TRANSPARENCY_COLLABORATIVE = 1;

	/**
	 * @const TRANSPARENCY_CONTENT_BASED transparency content based
	 */
	const TRANSPARENCY_CONTENT_BASED = 2;

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
	 * returns the recommendation item
	 *
	 * @return Item
	 * @since 1.0.0
	 */
	public function getItem(): Item {
		return $this->item;
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
	 * returns the user for that the recommendation is made
	 *
	 * @return IUser
	 * @since 1.0.0
	 */
	public function getUser(): IUser {
		return $this->user;
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
	 * @param Similarity $collaborative
	 * @since 1.0.0
	 */
	public function setCollaborative(Similarity $collaborative) {
		$this->collaborative = $collaborative;
	}

	/**
	 * Returns the collaborative similarity value
	 *
	 * @return Similarity
	 * @since 1.0.0
	 */
	public function getCollaborative(): Similarity {
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
	 * @param Similarity $contentBased
	 * @since 1.0.0
	 */
	public function setContentBased(Similarity $contentBased) {
		$this->contentBased = $contentBased;
	}

	/**
	 * Returns the content based similarity value
	 *
	 * @return Similarity
	 * @since 1.0.0
	 */
	public function getContentBased(): Similarity {
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
		return "
				[#itemId#][#{$this->item->getId()}#]
				[#itemName#][#{$this->item->getName()}#]
				[#userId#][#{$this->user->getUID()}#]
				[#collaborativeValue#][#{$this->collaborative->getValue()}#]
				[#collaborativeWeight#][#" . Application::COLLABORATIVE_FILTERING_WEIGHT . "#]
				[#contentBasedValue#][#{$this->contentBased->getValue()}#]
				[#contentBasedWeight#][#" . Application::CONTENT_BASED_RECOMMENDATION_WEIGHT . "#]
				[#groupWeight#][#{$this->groupWeight}#]
				[#recommendation#][#$val#]";
	}

	/**
	 * calculates the weighted average of an HybridItem
	 *
	 * @param HybridItem $hybrid
	 * @return float
	 * @since 1.0.0
	 */
	public static function weightedAverage(HybridItem $hybrid): float {
		$contentBasedResult = Application::CONTENT_BASED_RECOMMENDATION_WEIGHT * $hybrid->getContentBased()->getValue();
		$collaborativeResult = Application::COLLABORATIVE_FILTERING_WEIGHT * $hybrid->getCollaborative()->getValue();
		$weightedAverage = $hybrid->getGroupWeight() * ($contentBasedResult + $collaborativeResult);
		return $weightedAverage;
	}

	/**
	 * decides whether the item is recommendaable or not
	 *
	 * @return bool item is recommendable
	 * @since 1.0.0
	 */
	public function isRecommandable(): bool {
		$val = HybridItem::weightedAverage($this);
		if ($val > Application::RECOMMENDATION_THRESHOLD) {
			return true;
		}
		return false;
	}

	/**
	 * This method returns the transparency code for the recommendation. 'Transparency'
	 * is defined as the a hint for the user why he gets the item recommended.
	 * There are actually three possible transparency codes:
	 *
	 *    TRANSPARENCY_COLLABORATIVE = user gets item recommended due to
	 *        Collaborative Filtering
	 *    TRANSPARENCY_CONTENT_BASED = user gets item recommended due to keyword
	 *        overlap (content based recommendation)
	 *    TRANSPARENCY_BOTH = user gets item recommended due to both reasons
	 *
	 * Default return value is TRANSPARENCY_BOTH
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getTransparencyCode(): int {
		if (NumberUtil::compareFloat($this->collaborative->getValue(), $this->contentBased->getValue())) {
			return self::TRANSPARENCY_BOTH;
		}
		if (NumberUtil::floatGreaterThan($this->collaborative->getValue(), $this->contentBased->getValue())) {
			return self::TRANSPARENCY_COLLABORATIVE;
		}
		if (NumberUtil::floatGreaterThan($this->contentBased->getValue(), $this->collaborative->getValue())) {
			return self::TRANSPARENCY_CONTENT_BASED;
		}
		return self::TRANSPARENCY_BOTH;
	}
}
