<?php

namespace OCA\RecommendationAssistant\Recommendation;
/**
 * Class TimestampRatingCalculator
 *
 * @package OCA\RecommendationAssistant\Recommendation
 */
class TimestampRatingCalculator implements IRatingCalculator{
	/** @var \DateTime|null $timestamp */
	private $timestamp = 0;

	/**
	 * TimestampRatingCalculator constructor.
	 *
	 * @param int $timestamp
	 */
	public function __construct(int $timestamp){
		$this->timestamp = new \DateTime();
		$this->timestamp->setTimestamp($timestamp);
	}

	/**
	 * @return float
	 */
	public function getRating(): float{
		$now = new \DateTime();
		$diff = $now->diff($this->timestamp);
		$days = $diff->days;
		if($days <= 5) return 5;
		if($days > 5 && $days <= 10) return 4;
		if($days > 15 && $days <= 20) return 3;
		if($days > 20 && $days <= 25) return 2;
		if($days > 25 && $days <= 30) return 1;
		if($days > 30) return 0;
		return 0;
	}
}