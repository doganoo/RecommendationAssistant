<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 06.12.17
 * Time: 14:01
 */

namespace OCA\RecommendationAssistant\Recommendation;


use OCA\RecommendationAssistant\Interfaces\IComputable;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\KeywordList;

class OverlapCoefficientComputer implements IComputable {
	private $item = null;
	private $keywordList = null;

	public function __construct(Item $item, KeywordList $keywordList) {
		$this->item = $item;
		$this->keywordList = $keywordList;
	}

	/**
	 * Computes similiarty between two or more items
	 *
	 * @since 1.0.0
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