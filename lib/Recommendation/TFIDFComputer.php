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

use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\Keyword;
use OCA\RecommendationAssistant\Objects\KeywordList;

/**
 * TFIDFComputer class that computes the Term Frequency / Inverse Document Frequency
 * for an item.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class TFIDFComputer {
	/**
	 * @var Item $item
	 */
	private $item = null;

	/**
	 * @var ItemList $itemList
	 */
	private $itemList = null;

	/**
	 * Class constructor gets the item and itembase injected for that the
	 * TFIDF value has to be calculated.
	 *
	 * @param Item $item the item
	 * @param ItemList $itemBase the itembase
	 * @since 1.0.0
	 */
	public function __construct(
		Item $item,
		ItemList $itemBase) {
		$this->item = $item;
		$this->itemList = $itemBase;
	}

	/**
	 * Method implementation that is declared in the interface IComputable.
	 * This method implements the Term Frequency / Inverse Document Freqeuncy
	 * calculation algorithm for keywords that is assoociated to an item.
	 *
	 * @since 1.0.0
	 * @return KeywordList resulting array that contains the (trimmed) keywords and
	 * their TFIDF value
	 */
	public function compute() {
		$result = new KeywordList();
		$itemBaseSize = $this->itemList->size();

		/** @var Keyword $keyword */
		foreach ($this->item->getKeywordList() as $keyword) {
			if (trim($keyword->getKeyword()) == "") {
				continue;
			}
			$termFrequency = $this->item->countKeyword($keyword->getKeyword()) / $this->item->keywordSize();
			$count = $this->itemList->countKeyword($keyword->getKeyword());

			if ($count == 0) {
				$count = 1;
			}
			if ($count == $itemBaseSize) {
//				Logger::warn("number of keyword in list and number of items in list are the same. TFIDF is going to be 0");
			}
			$inverseDocumentFrequency = log10($itemBaseSize / $count);
			$tfIdf = $termFrequency * $inverseDocumentFrequency;
			if ($tfIdf < 0) {
				$tfIdf = 0;
			}
			$keyword->setTfIdf($tfIdf);
			$result->add($keyword);
		}
		return $result;
	}
}