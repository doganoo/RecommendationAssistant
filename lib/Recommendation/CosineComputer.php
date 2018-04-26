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
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Objects\Similarity;
use OCA\RecommendationAssistant\Util\Util;

/**
 * CosineComputer class that computes the similarity between two items.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class CosineComputer {
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
			$similarity = Util::createSimilarity(1.0, Similarity::SAME_COSINE_ITEMS, "the items are the same");
			return $similarity;
		}
		$denominatorA = 0;
		$denominatorB = 0;
		$numerator = 0;
		$denominator = 0;
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
			$numerator += $sourceRating * $targetRating;
			$powX = pow($sourceRating, 2);
			$powY = pow($targetRating, 2);
			$denominatorA += $powX;
			$denominatorB += $powY;
		}
		$denominator = sqrt($denominatorA) * sqrt($denominatorB);
		if ($denominator == 0) {
			$similarity = Util::createSimilarity(0.0, Similarity::NO_COSINE_SQUARE_POSSIBLE, "multiplication returned 0");
		} else {
			$similarity = Util::createSimilarity($numerator / $denominator, Similarity::VALID, "ok");
		}
		return $similarity;
	}
}