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

/**
 * Class that serves as a list for all items. ItemList implements the
 * \IteratorAggregate interface that is defined in the PHP core in order
 * to be iterable (in a foreach loop, for example).
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class ItemList implements \IteratorAggregate {

	/**
	 * @var array $itemList
	 */
	private $itemList = [];

	/**
	 * merges a different instance of ItemList to the actual one. The method
	 * calls the add() method in a loop in order to add the items.
	 *
	 * @param ItemList $itemList the item list that should be merged
	 * @since 1.0.0
	 */
	public function merge(ItemList $itemList) {
		foreach ($itemList as $item) {
			$this->add($item);
		}
	}

	/**
	 * adds an item to the list. The ratings of the new item will be transfered
	 * to the actual one if the item is already present in the list.
	 *
	 * @param Item $item the item that should be added to the list
	 * @return bool
	 * @since 1.0.0
	 */
	public function add(Item $item): bool {
		if (!$item->isValid()) {
			return false;
		}
		if (isset($this->itemList[$item->getId()])) {
			/** @var Item $item1 */
			$item1 = $this->itemList[$item->getId()];
			$item1->addRaters($item->getRaters());
			$this->itemList[$item->getId()] = $item1;
		} else {
			$this->itemList[$item->getId()] = $item;
		}
		return true;
	}

	/**
	 * returns the number of items that are in the list
	 *
	 * @since 1.0.0
	 * @return int number of items
	 */
	public function size(): int {
		return count($this->itemList);
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator() {
		return new \ArrayIterator($this->itemList);
	}

	public function getItems() {
		return $this->itemList;
	}
}