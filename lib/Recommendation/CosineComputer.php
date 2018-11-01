<?php
/**
 * @copyright Copyright (c) 2017, Dogan Ucar (dogan@dogan-ucar.de)
 * @license   GNU AGPL version 3 or any later version
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\RecommendationAssistant\Recommendation;

use doganoo\PHPAlgorithms\Algorithm\Traversal\PreOrder;
use doganoo\PHPUtil\Util\NumberUtil;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\Rater;

/**
 * CosineComputer class that computes the similarity between two items.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since   1.0.0
 */
class CosineComputer{
	/**
	 * @param Item $item
	 * @param Item $item2
	 *
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException
	 */
	public function compute(Item &$item, Item &$item2){
		$preOrder = new PreOrder($item->getRaters());
		$denominatorA = 0;
		$denominatorB = 0;
		$numerator = 0;
		$preOrder->setCallable(function (Rater $rater) use (
			&$item
			, &$item2
			, &$denominatorA
			, &$denominatorB
			, &$numerator
		){
			$node = $item2->getRaters()->search($rater);
			if(null === $node) return;
			$sourceRating = $rater->getRating();
			/** @var Rater $otherRater */
			$otherRater = $node->getValue();
			$targetRating = $otherRater->getRating();
			$numerator += $sourceRating * $targetRating;
			$powX = pow($sourceRating, 2);
			$powY = pow($targetRating, 2);
			$denominatorA += $powX;
			$denominatorB += $powY;
		});
		$preOrder->traverse();
		$denominator = sqrt($denominatorA) * sqrt($denominatorB);
		if(NumberUtil::compareFloat(0, $denominator)){
			$val = 0;
		} else{
			$val = $numerator / $denominator;
		}
		$item->addSimiliarity($item2, $val);
		$item2->addSimiliarity($item, $val);
	}
}