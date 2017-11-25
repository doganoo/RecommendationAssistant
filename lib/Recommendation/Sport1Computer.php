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
 * Sport1Computer class that computes the similarity between two items based on
 * their keywords.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class Sport1Computer implements IComputable {
	/**
	 * @var Item $item
	 */
	private $item = null;

	/**
	 * @var Item $item1
	 */
	private $item1 = null;

	/**
	 * @var TFIDFComputer $tfIdfComputer
	 */
	private $tfIdfComputer = null;

	/**
	 * @var array $itemBase
	 */
	private $itemBase = null;

	/**
	 * Class constructor gets two items and the whole itembase injected.
	 * The two items are used for similarity measurement whereas the itembase
	 * is used to get averages out of them.
	 *
	 * @param Item $item the first item
	 * @param Item $item1 the second item
	 * @param ItemList $itemBase the itembase represents all items
	 * @since 1.0.0
	 */
	public function __construct(Item $item, Item $item1, ItemList $itemBase) {
		//TODO do not inject itembase!
		$this->item = $item;
		$this->item1 = $item1;
		$this->itemBase = $itemBase;
	}

	/**
	 * Method implementation that is declared in the interface IComputable.
	 * This method implements the algorithm defined in a work of the Sport1
	 * media house.
	 * The computation is based on keywords that are extracted out of the items.
	 *
	 * @since 1.0.0
	 * @return similarity value of item and item1
	 */
	public function compute() {
		$result = $this->computeTfIdf($this->item);
		$result1 = $this->computeTfIdf($this->item1);
		return $this->doCompute($result, $result1);
	}

	/**
	 * computes the Term Frequency / Inverse Document Frequency for a set
	 * of keywords. Each keyword gets an TFIDF value that represents its
	 * tier within the item / itembase.
	 *
	 * @param Item $item
	 * @since 1.0.0
	 * @return TFIDF value for the item
	 */
	private function computeTfIdf(Item $item) {
		$this->tfIdfComputer = new TFIDFComputer($item, $this->itemBase);
		$result = $this->tfIdfComputer->compute();
		return $result;
	}

	/**
	 * executes the Sport1 algorithm
	 *
	 * @param $result the TFIDF value for item
	 * @param $result1 the TFIDF value for item1
	 * @since 1.0.0
	 * @return similarity value for item and item1
	 */
	private function doCompute($result, $result1) {
		$upper = 0;
		$lower1 = 0;
		$lower2 = 0;
		foreach ($result as $keyword => $value) {
			$val = 0;
			if (isset($result1[$keyword])) {
				$val = $result1[$keyword];
			}
			$upper += $value * $val;
			$lower1 += pow($value, 2);
		}
		foreach ($result1 as $keyword => $value) {
			$lower2 += pow($value, 2);
		}
		$sqrt = sqrt($lower1 * $lower2);
		if ($sqrt == 0) {
			return 0;
		}
		return $upper / $sqrt;
	}
}