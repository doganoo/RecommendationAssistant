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
	private $keywordList = [];

	private $maxTfIdf = 0;


	/**
	 * adds an keyword to the list. The highest TFIDF value will remain in
	 * the list for a specific keyword.
	 *
	 * @param Keyword $keyword the item that should be added to the list
	 * @since 1.0.0
	 */
	public function add(Keyword $keyword) {
		if (isset($this->keywordList[$keyword->getKeyword()])) {
			/** @var Keyword $oldValue */
			$oldValue = $this->keywordList[$keyword->getKeyword()];
			if ($keyword->getTfIdf() > $oldValue->getTfIdf()) {
				$keyword->setCount($oldValue->getCount() + 1);
				$this->keywordList[$keyword->getKeyword()] = $keyword;
			} else {
				$oldValue->setCount($oldValue->getCount() + 1);
				$this->keywordList[$keyword->getKeyword()] = $oldValue;
			}
		} else {
			$this->keywordList[$keyword->getKeyword()] = $keyword;
		}
		if ($this->maxTfIdf < $keyword->getTfIdf()) {
			$this->maxTfIdf = $keyword->getTfIdf();
		}
	}

	public function getKeyword(string $keyword) {
		return isset($this->keywordList[$keyword]) ? $this->keywordList[$keyword] : null;
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
		$count = 0;
		array_walk($this->keywordList, function (Keyword $keyword, string $key) use (&$count) {
			$count += $keyword->getCount();
		}, ARRAY_FILTER_USE_BOTH);
		return $count;
	}

	/**
	 * removes all words that have a TFIDF value equal to 0. Then, 1/10 of the
	 * list is removed.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function removeStopwords(): int {
		$previousNumber = $this->size();
		/**
		 * PHP core function array_filter removes all array elements defined by
		 * a callback. The callback function removes all TFIDFItems that have
		 * the TFIDF value of 0 or have a TFIDF value less then 1/3 of the max
		 * TFIDF value.
		 */
		$this->keywordList = array_filter($this->keywordList,
			function (Keyword $item, string $keyword) {
				$precision = 10;
				$floatVal = floatval($item->getTfIdf());
				$zero = round(0, $precision, PHP_ROUND_HALF_EVEN);
				$tfidf = round($floatVal, $precision, PHP_ROUND_HALF_EVEN);
				$maxTfIdfThreshold = round($this->maxTfIdf * Application::STOPWORD_REMOVAL_PERCENTAGE, $precision, PHP_ROUND_HALF_EVEN);
				return ($tfidf > $zero) || ($tfidf > $maxTfIdfThreshold);
			}, ARRAY_FILTER_USE_BOTH);
		$actualNumber = $this->size();
		$filteredNumber = ($previousNumber - $actualNumber);
		return \abs($filteredNumber);
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