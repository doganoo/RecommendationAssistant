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
	 * @param Keyword $keyword the item that should be added to the list
	 * @since 1.0.0
	 */
	public function add(Keyword $keyword) {
		//TODO check whether this approach (survival of the highest) is valid
		if (isset($this->keywordList[$keyword->getKeyword()])) {
			$oldValue = $this->keywordList[$keyword->getKeyword()];
			if ($keyword->getTfIdf() > $oldValue->getTfIdf()) {
				$this->keywordList[$keyword->getKeyword()] = $keyword;
			}
		} else {
			$this->keywordList[$keyword->getKeyword()] = $keyword;
		}
	}

	/**
	 * this methods merges two keyword lists to one. The method iterates
	 * over the keyword list and calls the add() method.
	 *
	 * @param KeywordList $keywordList
	 * @since 1.0.0
	 */
	public function merge(KeywordList $keywordList) {
		/** @var Keyword $keyword */
		foreach ($keywordList as $keyword) {
			$this->add($keyword);
		}
	}

	/**
	 * returns the number of keywords that are in the list
	 *
	 * @return int number of items
	 * @since 1.0.0
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
		uasort($this->keywordList, function (Keyword $a, Keyword $b) {
			$aValue = floatval($a->getTfIdf());
			$bValue = floatval($b->getTfIdf());
			$epsilon = 0.00001;
			$equals = abs($aValue - $bValue) < $epsilon;
			//never compare two floating point numbers with == or ===.
			//The reason lies in the limited precision of the numbers.
			//more information are available at:
			//http://php.net/manual/en/language.types.float.php

			if ($equals) {
				return 0;
			}
			return ($aValue > $bValue) ? -1 : 1;
		});
	}

	/**
	 * removes all words that have a TFIDF value equal to 0. Then, 1/10 of the
	 * list is removed.
	 *
	 * @since 1.0.0
	 */
	public function removeStopwords() {
		/**
		 * PHP core function array_filter removes all array elements defined by
		 * a callback. The callback function removes all TFIDFItems that have
		 * the TFIDF value of 0.
		 */
		$this->keywordList = array_filter($this->keywordList,
			function (Keyword $item, string $keyword) {
				$floatVal = floatval($item->getTfIdf());
				//TODO never compare floats with ==
				//return (abs($floatVal - 0.0) < 0.1);
				return $floatVal !== 0.0;
			}, ARRAY_FILTER_USE_BOTH);
		/**
		 * the following code removes the last 1/10 of the keywords that are
		 * in the list. The code is useless actually because we want to see
		 * the results without.
		 */
		//$this->sort();
		//$size = $this->size();
		//$last = round(($size / 10), 0, PHP_ROUND_HALF_UP);
		//$this->keywordList = array_slice($this->keywordList, 0, $size - $last, true);
	}

	/**
	 * returns the TFIDF value for a single keyword. The method will return the
	 * value of 0 if the keyword is not present in the list.
	 *
	 * @param string $keyword the keyword that is searched for
	 * @return int|Keyword
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
		if ($this->keywordList == null) {
			return new \ArrayIterator([]);
		}
		return new \ArrayIterator($this->keywordList);
	}

	/**
	 * returns all keywords as an array
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getKeywords() {
		$array = [];
		if ($this->keywordList == null) {
			return $array;
		}
		foreach ($this->keywordList as $keyword) {
			$array[] = $keyword->getKeyword();
		}
		return $array;
	}
}