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
use OCA\RecommendationAssistant\Objects\KeywordList;

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
	 * OverlapCoefficientComputer constructor.
	 *
	 * @param Item $item
	 * @param KeywordList $keywordList
	 */
	public function __construct(Item $item, KeywordList $keywordList) {
		$this->item = $item;
		$this->keywordList = $keywordList;
	}

	/**
	 * Method implementation that is declared in the interface IComputable.
	 * This method implements the Overlap Coefficient Algorithm.
	 *
	 * @since 1.0.0
	 * @return float the overlap coefficient
	 */
	public function compute() {
		$arr = array_intersect($this->item->getKeywords(), $this->keywordList->getKeywords());
		$arr = array_unique($arr);
		$count = count($arr);
		$lower = $this->item->keywordSize() > $this->keywordList->size() ? $this->keywordList->size() : $this->keywordList->size();
		if ($count == 0 || $lower == 0) {
			return 0;
		}
		return $count / $lower;
	}
}