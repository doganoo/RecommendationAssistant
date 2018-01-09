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
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Objects\Similarity;

/**
 * CosineComputer class that computes the similarity between two items.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class CosineComputer implements IComputable {
	/**
	 * @var Item $sourceItem the first item
	 */
	private $sourceItem = null;

	/**
	 * @var Item $targetItem the first item
	 */
	private $targetItem = null;

	/**
	 * Class constructor gets two items injected
	 *
	 * @param Item $sourceItem the item for that the prediction is made
	 * @param Item $targetItem the item to that $sourceItem is compared
	 * @since 1.0.0
	 */
	public function __construct(
		Item $sourceItem,
		Item $targetItem) {
		$this->sourceItem = $sourceItem;
		$this->targetItem = $targetItem;
	}

	/**
	 * Computes similiarty between two items. This method returns the value
	 * 1 if the items are equal.
	 *
	 * @since 1.0.0
	 */
	public function compute(): Similarity {
		$similarity = new Similarity();
		if ($this->sourceItem->equals($this->targetItem)) {
			$similarity->setValue(1.0);
			$similarity->setStatus(Similarity::SAME_COSINE_ITEMS);
			$similarity->setDescription("the items are the same");
			return $similarity;
		}
		$lowerA = 0;
		$lowerB = 0;
		$upper = 0;
		$lower = 0;
		/** @var Rater $rater */
		foreach ($this->sourceItem->getRaters() as $rater) {
			$yValid = $this->targetItem->getRater(
				$rater->getUser()->getUID()
			)->isValid();
			if (!$yValid) {
				continue;
			}
			$sourceRating = $rater->getRating();
			$targetRating = $this->targetItem->getRater($rater->getUser()->getUID())->getRating();

			$upper += $sourceRating * $targetRating;
			$powX = pow($sourceRating, 2);
			$powY = pow($targetRating, 2);
			$lowerA += $powX;
			$lowerB += $powY;
		}
		$lower = sqrt($lowerA) * sqrt($lowerB);
		if ($lower == 0) {
			$similarity->setValue(0.0);
			$similarity->setStatus(Similarity::NO_COSINE_SQUARE_POSSIBLE);
			$similarity->setDescription("multiplication of sqrt of both returned 0");
		} else {
			$similarity->setValue($upper / $lower);
			$similarity->setStatus(Similarity::VALID);
			$similarity->setDescription("ok");
		}
		return $similarity;
	}
}