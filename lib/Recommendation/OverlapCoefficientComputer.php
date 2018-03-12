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


use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Interfaces\IComputable;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\KeywordList;
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Objects\Similarity;
use OCA\RecommendationAssistant\Util\Util;

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
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidSimilarityValueException
	 */
	public function compute(): Similarity {
		$similarity = new Similarity();

		if (Application::DISABLE_CONTENT_BASED_RECOMMENDATION) {
			$similarity->setValue(0.0);
			$similarity->setDescription("content based recommendation is disabled");
			$similarity->setStatus(Similarity::DISABLED_CONTENT_BASED_RECOMMENDATION);
			return $similarity;
		}
		$tfIdf = new TFIDFComputer($this->item, $this->itemList);

		/** @var KeywordList $itemKeywords */
		$itemKeywords = $tfIdf->compute();
		$itemKeywords->removeStopwords();
		$arr = array_intersect($itemKeywords->getKeywords(), $this->keywordList->getKeywords());
		$arr = array_unique($arr);
		$numerator = count($arr);
		//this source code part means: use the amount of item keywords if the
		//item has less keywords than the user profile. Otherwise use the amount
		//of the user profile
		$denominator = $itemKeywords->size() > $this->keywordList->size() ? $this->keywordList->size() : $itemKeywords->size();

		if ($numerator == 0) {
			$similarity = Util::createSimilarity(0.0, Similarity::NO_OVERLAPPING_KEYWORDS, "no overlapping keywords found");
		}
		if ($denominator == 0) {
			$similarity = Util::createSimilarity(0.0, Similarity::ITEM_OR_USER_PROFILE_EMPTY, "no keywords in item / user profile");
		}
		if ($numerator > 0 && $denominator > 0) {
			/*
			 * since overlap coefficient measures the similarity of two
			 * items in range between 0 and 1 and the cosine computer can compute
			 * between greater ranges, we need to define a factor that is the
			 * upper limit of possible ratings. For example, if the range is between
			 * 0 and 5, all similarity values that are computed by this class
			 * have to be multiplied by 5.
			 */
			$factor = Rater::RATING_UPPER_LIMIT;
			$similarity = Util::createSimilarity(($numerator / $denominator) * $factor, Similarity::VALID, "valid calculation");
		}
		return $similarity;
	}
}