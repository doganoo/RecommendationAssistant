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
	 * @var Item $x the first item
	 */
	private $x = null;

	/**
	 * @var Item $y the first item
	 */
	private $y = null;

	public function __construct(Item $x, Item $y) {
		$this->x = $x;
		$this->y = $y;
	}

	/**
	 * Computes similiarty between two items
	 *
	 * @since 1.0.0
	 */
	public function compute() {
		if ($this->x->equals($this->y)) {
			return 1;
		}
		$lowerA = 0;
		$lowerB = 0;
		$upper = 0;
		$lower = 0;
		/** @var Rater $rater */
		foreach ($this->x->getRaters() as $rater) {
			$rater->getRating();
			$yValid = $this->y->getRater($rater->getUser()->getUID())->isValid();
			if (!$yValid) {
				continue;
			}
			$raterY = $this->y->getRater($rater->getUser()->getUID());
			$x = $rater->getRating();
			$y = $raterY->getRating();

			$upper += $x * $y;
			$lowerA += pow($x, 2);
			$lowerB += pow($y, 2);
		}
		$lower = sqrt($lowerA) * sqrt($lowerB);
		if ($lower == 0) {
			return 0;
		}
		return $upper / $lower;
	}
}