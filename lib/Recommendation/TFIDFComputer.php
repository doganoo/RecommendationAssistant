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
use OCA\RecommendationAssistant\Objects\KeywordList;
use OCA\RecommendationAssistant\Objects\TFIDFItem;

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
	 * @var ItemList $itemBase
	 */
	private $itemBase = null;

	/**
	 * @var KeywordList $result
	 */
	private $result = null;

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
		$this->result = new KeywordList();
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
		$itemBaseSize = $this->itemBase->size();
		foreach ($this->item->getKeywords() as $keyword) {
			if (trim($keyword) == "") {
				continue;
			}
			$tfIdfItem = new TFIDFItem();
			$count = $this->itemBase->countKeyword($keyword);
			$termFrequency = $this->item->countKeyword($keyword) / $this->item->keywordSize();

			if ($count == 0) {
				$count = 1;
			}
			$inverseDocumentFrequency = log10($itemBaseSize / $count);
			$tfIdf = $termFrequency * $inverseDocumentFrequency;
			if ($tfIdf < 0) {
				$tfIdf = 0;
			}
			$tfIdfItem->setValue($tfIdf);
			$tfIdfItem->setKeyword($keyword);
			$this->result->add($tfIdfItem);
		}
		$this->result->removeStopwords();
		return $this->result;
	}
}