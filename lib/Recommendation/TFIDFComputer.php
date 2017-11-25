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

namespace OCA\RecommendationAssistant\Recommendation;

use OCA\RecommendationAssistant\Interfaces\IComputable;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;

/**
 * TFIDFComputer class that computes the Term Frequency / Inverse Document Frequency
 * for an item.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
//TODO: check whether the implemenation of IComputable is necessary
class TFIDFComputer implements IComputable {
	/**
	 * @var Item $item
	 */
	private $item = null;

	/**
	 * @var ItemList $itemBase
	 */
	private $itemBase = null;

	/**
	 * @var array $result
	 */
	private $result = [];

	/**
	 * Class constructor gets the item and itembase injected for that the
	 * TFIDF value has to be calculated.
	 *
	 * @param Item $item the item
	 * @param ItemList $itemBase the itembase
	 * @since 1.0.0
	 */
	public function __construct(Item $item, ItemList $itemBase) {
		$this->item = $item;
		$this->itemBase = $itemBase;
	}

	/**
	 * Method implementation that is declared in the interface IComputable.
	 * This method implements the Term Frequency / Inverse Document Freqeuncy
	 * calculation algorithm for keywords that is assoociated to an item.
	 *
	 * @since 1.0.0
	 * @return array resulting array that contains the (trimmed) keywords and
	 * their TFIDF value
	 */
	public function compute() {
		$termFrequency = count($this->item->getKeywords());
		$itemBaseSize = $this->itemBase->size();
		foreach ($this->item->getKeywords() as $keyword) {
			if (trim($keyword) == "") {
				continue;
			}
			$count = $this->countKeywordInItemBase($keyword);
			if ($count == 0) {
				$count = 1;
			}
			$inverseDocumentFrequency = log($itemBaseSize / $count);
			$tfIdf = $termFrequency * $inverseDocumentFrequency;
			$this->result[$keyword] = $tfIdf;
		}
		$this->trim();
		return $this->result;
	}

	/**
	 * This methods removes the last X entries out of an array. The assumption is
	 * that the last X elements of the keyword list are stopwords that are not
	 * needed for further steps.
	 *
	 * @since 1.0.0
	 */
	private function trim() {
		$resultSize = count($this->result);
		$x = $resultSize / 3;
		$x = round($x, PHP_ROUND_HALF_UP);
		$this->result = array_slice($this->result, 0, $resultSize - $x, true);
	}

	/**
	 * counts the number of occurances of an keyword within the itembase
	 *
	 * @param string $keyword the keyword that is searched for
	 * @since 1.0.0
	 * @return int number of occurances of $keyword within the itembase
	 */
	private function countKeywordInItemBase(string $keyword) {
		$i = 0;
		foreach ($this->itemBase as $item) {
			$keywords = $item->getKeywords();
			if (array_key_exists($keyword, $keywords)) {
				$i++;
			}
		}
		return $i;
	}
}