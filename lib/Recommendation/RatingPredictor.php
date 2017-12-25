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
use OCA\RecommendationAssistant\Objects\ItemToItemMatrix;
use OCP\IUser;

/**
 * RatingPredictor class that computes the weighted average of the results
 * of the CosineComputer class.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class RatingPredictor {
	/**
	 * contains all items that are 'similar' to item
	 *
	 * @var ItemList $itemList all items that are similar to $item
	 */
	private $itemList = null;

	/**
	 * the user for whom the predicition should made
	 *
	 * @var IUser $user the user
	 */
	private $user = null;

	/**
	 * the item for that the prediction is performed
	 *
	 * @var Item $item the item
	 */
	private $item = null;

	/**
	 * the item to item matrix that contains the similarities
	 *
	 * @var ItemToItemMatrix the matrix
	 */
	private $matrix = null;

	/**
	 * Class constructor gets multiple instances injected
	 *
	 * @param Item $item the item for that the prediction is to be made
	 * @param IUser $user the user for whom the prediciton is to be made
	 * @param ItemList $itemList all items that are similar to $item
	 * @param ItemToItemMatrix $matrix the matrix that contains the similarities
	 * across multiple items
	 * @since 1.0.0
	 */
	public function __construct(
		Item $item,
		IUser $user,
		ItemList $itemList,
		ItemToItemMatrix $matrix) {
		$this->item = $item;
		$this->user = $user;
		$this->itemList = $itemList;
		$this->matrix = $matrix;
	}

	/**
	 * This method predicts the recommendation value of $item for $user using
	 * the weighted average method.
	 *
	 * @return float
	 * @since 1.0.0
	 */
	public function predict(): float {
		$upper = 0;
		$lower = 0;
		/** @var Item $item1 */
		foreach ($this->itemList as $item1) {
			if ($this->item->equals($item1)) {
				continue;
			}
			if (!$item1->getRater($this->user->getUID())->isValid()) {
				continue;
			}
			$rating = $item1->getRater($this->user->getUID())->getRating();
			$sim = $this->matrix->get($this->item, $item1);
			$upper += $rating * $sim;
			$lower += $rating;
		}
		if ($lower == 0) {
			return 0.0;
		}
		$return = floatval($upper / $lower);
		return $return;
	}

}