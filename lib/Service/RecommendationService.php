<?php

namespace OCA\RecommendationAssistant\Service;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\Recommendation;
use OCA\RecommendationAssistant\Recommendation\CosineComputer;
use OCA\RecommendationAssistant\Recommendation\RatingPredictor;

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
		ConsoleLogger::debug("cache results!");
		$ratingPredictor = new RatingPredictor();
		$recommendation = $ratingPredictor->predict($itemList, $uid);
		return $recommendation;
	}
}