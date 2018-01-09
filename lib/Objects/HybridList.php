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
use Traversable;

/**
 * Class that serves as a list for all items. HybridList implements the
 * \IteratorAggregate interface that is defined in the PHP core in order
 * to be iterable (in a foreach loop, for example).
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class HybridList implements \IteratorAggregate {
	/**
	 * @var array $hybridList array that contains all HybridItems
	 */
	private $hybridList = [];

	/**
	 * returns a HybridItem for a given item-user pair. If the list
	 * does not contain an item, the method will create a new instance
	 * and return it.
	 *
	 * @param Item $item the item for that the HybridItem is requested
	 * @param IUser $user the user for that the HybridItem is requested
	 * @return HybridItem
	 * @since 1.0.0
	 */
	public function getHybridByUser(Item $item, IUser $user): HybridItem {
		if (isset($this->hybridList[$user->getUID()][$item->getId()])) {
			return $this->hybridList[$user->getUID()][$item->getId()];
		} else {
			return new HybridItem();
		}
	}

	/**
	 * adds a new HybridItem for a given item-user pair to the list.
	 *
	 * @param HybridItem $hybrid the item that should be added
	 * @param IUser $user the user for that the HybridItem is added
	 * @param Item $item the item for that the HybridItem is added
	 * @since 1.0.0
	 */
	public function add(HybridItem $hybrid, IUser $user, Item $item) {
		$this->hybridList[$user->getUID()][$item->getId()] = $hybrid;
	}

	/**
	 * returns the size of $hybridList. If $recursive is true the method
	 * will count multidimensional arrays.
	 *
	 * @param bool $recursive boolean for multidimensional count
	 * @return int
	 * @since 1.0.0
	 */
	public function size(bool $recursive = false): int {
		$countMode = $recursive == true ? COUNT_RECURSIVE : COUNT_NORMAL;
		return count($this->hybridList, $countMode);
	}

	public function removeNonRecommendable() {
		/**
		 * @var string $userId
		 * @var array $array
		 */
		foreach ($this->hybridList as $userId => $array) {
			/**
			 * @var string $itemId
			 * @var HybridItem $hybridItem
			 */
			foreach ($array as $itemId => $hybridItem) {
				if (!$hybridItem->isRecommandable()) {
					unset($this->hybridList[$userId][$itemId]);
				}
			}
			if (count($this->hybridList[$userId]) == 0) {
				unset($this->hybridList[$userId]);
			}
		}
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator() {
		return new \ArrayIterator($this->hybridList);
	}
}