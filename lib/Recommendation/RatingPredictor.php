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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use doganoo\PHPUtil\Util\NumberUtil;
use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\Recommendation;

/**
 * RatingPredictor class that computes the weighted average of the results
 * of the CosineComputer class.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since   1.0.0
 */
class RatingPredictor {
	/**
	 * @param ArrayList $itemList
	 * @param string $uid
	 *
	 * @return Recommendation
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\IndexOutOfBoundsException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidSearchComparisionException
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	public function predict(ArrayList $itemList, string $uid): Recommendation {
		$size = $itemList->size();
		$recommendation = new Recommendation();
		$recommendation->setUserId($uid);
		for ($i = 0; $i < $size; $i++) {
			$numerator = 0;
			$denominator = 0;
			/** @var Item $item */
			$item = $itemList->get($i);
			for ($j = $i + 1; $j < $size; $j++) {
				/** @var Item $item1 */
				$item1 = $itemList->get($j);
				$sim = $item->similartyById($item1->getId());
				if (null === $sim) continue;
				$rater1 = $item1->getRaterById($uid);
				if (null === $rater1) continue;
				$numerator += $sim * $rater1->getRating();
				$denominator += $sim;
			}

			if (NumberUtil::compareFloat(0, $denominator)) continue;
			$val = $numerator / $denominator;
			if (NumberUtil::floatGreaterThan(Application::RECOMMENDATION_THRESHOLD, $val))
				$recommendation->addRecommendation($item);
		}
		return $recommendation;
	}
}