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
use OCA\RecommendationAssistant\Objects\ConsoleLogger;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\KeywordList;
use OCA\RecommendationAssistant\Objects\Similarity;

/**
 * OverlapCoefficientComputer class that computes the similarity between two items
 * based on keywords that are associated to the items.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class OverlapCoefficientComputer implements IComputable {
	/**
	 * @var Item the item for that the similarity should be computed
	 */
	private $item = null;

	/**
	 * @var KeywordList the keywordList that represents the user base
	 */
	private $keywordList = null;

	/**
	 * @var ItemList $itemList the entire item base
	 */
	private $itemList = null;

	/**
	 * OverlapCoefficientComputer constructor.
	 *
	 * @param Item $item
	 * @param ItemList $itemList the entire item base
	 * @param KeywordList $keywordList
	 */
	public function __construct(
		Item $item,
		ItemList $itemList,
		KeywordList $keywordList) {
		$this->item = $item;
		$this->itemList = $itemList;
		$this->keywordList = $keywordList;
	}

	/**
	 * Method implementation that is declared in the interface IComputable.
	 * This method implements the Overlap Coefficient Algorithm.
	 *
	 * @since 1.0.0
	 * @return Similarity the similarity object that represents the similarity
	 */
	public function compute(): Similarity {
		$similarity = new Similarity();
		if ($this->item->keywordSize() === $this->itemList->size()) {
			$similarity->setValue(0.0);
			$similarity->setStatus(Similarity::NOT_ENOUGH_ITEMS_IN_ITEM_BASE);
			$similarity->setDescription("number of items and item base is equal");
		}
		$tfIdf = new TFIDFComputer($this->item, $this->itemList);
		/** @var KeywordList $itemKeywords */
		$itemKeywords = $tfIdf->compute();
		$itemKeywords->sort();
		$itemKeywords->removeStopwords();
		$arr = array_intersect($itemKeywords->getKeywords(), $this->keywordList->getKeywords());
		$arr = array_unique($arr);
		$count = count($arr);
		//this source code part means: use the amount of item keywords if the
		//item has more keywords than the user profile. Otherwise use the amount
		//of the user profile
		$lower = $itemKeywords->size() > $this->keywordList->size() ? $this->keywordList->size() : $itemKeywords->size();
		$value = 0.0;

		if ($count == 0) {
			$similarity->setValue(0.0);
			$similarity->setStatus(Similarity::NO_OVERLAPPING_KEYWORDS);
			$similarity->setDescription("no overlapping keywords found");
		}
		if ($lower == 0) {
			$similarity->setValue(0.0);
			$similarity->setStatus(Similarity::ITEM_OR_USER_PROFILE_EMPTY);
			$similarity->setDescription("no keywords in item / user profile");
		}
		if ($count > 0 && $lower > 0) {
			$similarity->setValue($count / $lower);
			$similarity->setStatus(Similarity::VALID);
			$similarity->setDescription("valid calculation");
		}
		return $similarity;
	}
}