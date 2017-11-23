<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 02.11.17
 * Time: 17:07
 */

namespace OCA\DoganMachineLearning\Recommendation;

use OCA\DoganMachineLearning\Interfaces\IComputable;
use OCA\DoganMachineLearning\Objects\Item;
use OCA\DoganMachineLearning\Objects\Rater;

class PearsonComputer implements IComputable {
	private $x = null;
	private $y = null;

	public function __construct(Item $x, Item $y) {
		$this->x = $x;
		$this->y = $y;

	}

	public function compute() {
		$avgX = $this->averageRating($this->x->getRaters());
		$avgY = $this->averageRating($this->y->getRaters());
		$upper = 0;
		$lowerX = 0;
		$lowerY = 0;
		$lowerTotal = 0;
		foreach ($this->x->getRaters() as $rater) {
			$y = Rater::NO_LIKE;
			$x = ($rater->getRating() - $avgX);
			$uid = $rater->getUser()->getUID();
			$raterPresent = $this->y->raterPresent($uid);
			if ($raterPresent) {
				$y = $this->y->getRater($uid)->getRating();
			}
			$y = ($y - $avgY);
			$upper = $upper + ($x * $y);
			$lowerX = $lowerX + pow($x, 2);
			$lowerY = $lowerY + pow($y, 2);
		}
		$lowerTotal = sqrt($lowerX * $lowerY);
		if ($lowerTotal == 0) {
			return 0;
		}
		return $upper / $lowerTotal;
	}

	private function averageRating(array $raters) {
		$sum = 0;
		$i = 0;
		foreach ($raters as $rater) {
			if ($rater instanceof Rater) {
				$sum = $sum + $rater->getRating();
				$i++;
			}
		}

		if ($i == 0) {
			return 0;
		}
		return $sum / $i;
	}

}