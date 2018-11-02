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

namespace OCA\RecommendationAssistant\Service;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\Recommendation;
use OCA\RecommendationAssistant\Recommendation\CosineComputer;
use OCA\RecommendationAssistant\Recommendation\RatingPredictor;

/**
 * Class RecommendationService
 *
 * @package OCA\RecommendationAssistant\Service
 */
class RecommendationService{
	/**
	 * @param ArrayList $list
	 *
	 * @return ArrayList
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\IndexOutOfBoundsException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException
	 */
	public function computeCosine(ArrayList $list): ArrayList{
		$size = $list->size();
		$cosineComputer = new CosineComputer();
		for($i = 0; $i < $size; $i ++){
			/** @var Item $item */
			$item = $list->get($i);
			for($j = $i + 1; $j < $size; $j ++){
				/** @var Item $item1 */
				$item1 = $list->get($j);
				$cosineComputer->compute($item, $item1);
				$list->set($j, $item1);
				$list->set($i, $item);
			}
		}
		return $list;
	}

	/**
	 * @param ArrayList $itemList
	 * @param string    $uid
	 *
	 * @return Recommendation
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\IndexOutOfBoundsException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidSearchComparisionException
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	public function predictForUser(ArrayList $itemList, string $uid): Recommendation{
		$ratingPredictor = new RatingPredictor();
		$recommendation = $ratingPredictor->predict($itemList, $uid);
		return $recommendation;
	}
}