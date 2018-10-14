<?php

namespace OCA\RecommendationAssistant\Recommendation;
/**
 * Interface IRatingCalculator
 *
 * @package OCA\RecommendationAssistant\Recommendation
 */
interface IRatingCalculator{
	/**
	 * @return float
	 */
	public function getRating(): float;
}