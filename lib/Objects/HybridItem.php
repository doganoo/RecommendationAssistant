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
	 * @var Item $item the item
	 */
	private $item = null;

	/**
	 * @var IUser $user the user
	 */
	private $user = null;

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
	 * sets the item for that the recommendation is made
	 *
	 * @param Item $item the item
	 * @since 1.0.0
	 */
	public function setItem(Item $item) {
		$this->item = $item;
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
	 * sets the user for whom the recommendation is made
	 *
	 * @param IUser $user the user
	 * @since 1.0.0
	 */
	public function setUser(IUser $user) {
		$this->user = $user;
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
		return $hybrid->getCollaborative()->getValue();
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
		return 1;
	}
}
