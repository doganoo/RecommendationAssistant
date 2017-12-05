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


use Traversable;

/**
 * Class that serves as a list for all keywords. KeywordList implements the
 * \IteratorAggregate interface that is defined in the PHP core in order
 * to be iterable (in a foreach loop, for example).
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class KeywordList implements \IteratorAggregate {
	/**
	 * @var array $keywordList
	 */
	private $keywordList = null;

	/**
	 * adds an keyword to the list. The highest TFIDF value will remain in
	 * the list for a specific keyword.
	 *
	 * @param TFIDFItem $tfIdfItem the item that should be added to the list
	 * @since 1.0.0
	 */
	public function add(TFIDFItem $tfIdfItem) {
		//TODO check whether this approach (survival of the highest) is valid
		if (isset($this->keywordList[$tfIdfItem->getKeyword()])) {
			$oldValue = $this->keywordList[$tfIdfItem->getKeyword()];
			if ($tfIdfItem->getValue() > $oldValue) {
				$this->keywordList[$tfIdfItem->getKeyword()] = $tfIdfItem;
			}
		} else {
			$this->keywordList[$tfIdfItem->getKeyword()] = $tfIdfItem;
		}
	}

	/**
	 * returns the number of keywords that are in the list
	 *
	 * @since 1.0.0
	 * @return int number of items
	 */
	public function size(): int {
		return count($this->keywordList);
	}

	/**
	 * sorts the keyword list
	 *
	 * @since 1.0.0
	 */
	public function sort() {
		uasort($this->keywordList, function (TFIDFItem $a, TFIDFItem $b) {
			//TODO convert values to numbers and use strict comparision
			if ($a->getValue() == $b->getValue()) {
				return 0;
			}
			return ($a->getValue() > $b->getValue()) ? -1 : 1;
		});
	}

	/**
	 * removes all words that have a TFIDF value equal to 0. Then, 1/10 of the
	 * list is removed.
	 *
	 * @since 1.0.0
	 */
	public function removeStopwords() {
		//TODO define "stopword": is it enough to remove keywords with value = 0?
		$this->keywordList = array_filter($this->keywordList, function (TFIDFItem $item, string $keyword) {
			return $item->getValue() != 0;
		}, ARRAY_FILTER_USE_BOTH);
		$this->sort();
		$size = $this->size();
		$last = round(($size / 10), 0, PHP_ROUND_HALF_UP);
		$this->keywordList = array_slice($this->keywordList, 0, $size - $last, true);
	}

	/**
	 * returns the TFIDF value for a single keyword. The method will return the
	 * value of 0 if the keyword is not present in the list.
	 *
	 * @param string $keyword the keyword that is searched for
	 * @since 1.0.0
	 */
	public function getValueByKeyword(string $keyword) {
		return isset($this->keywordList[$keyword]) ? $this->keywordList[$keyword] : 0;
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
		return new \ArrayIterator($this->keywordList);
	}
}