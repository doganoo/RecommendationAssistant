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
 * PearsonComputer class that computes the similarity between two items.
 * This class implements the IComputable interface in order to be a valid
 * instance that computes similarity.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class PearsonComputer implements IComputable {
	/**
	 * @var Item $x
	 */
	private $x = null;
	/**
	 * @var Item $y
	 */
	private $y = null;

	/**
	 * Class constructor gets two items injected for that the similarity
	 * should be calculated
	 *
	 * @param Item $x the first item
	 * @param Item $y the second item
	 * @since 1.0.0
	 */
	public function __construct(Item $x, Item $y) {
		$this->x = $x;
		$this->y = $y;

	}

	/**
	 * Method implementation that is declared in the interface IComputable.
	 * This method implements the Pearson Correlation Coefficient algorithm
	 * based on binary ratings (like, no like) from users.
	 * If one user has a appropriate item not rated yet, the rating will be
	 * represanted as "no like".
	 *
	 * @return float similarity value of item x and item y
	 * @since 1.0.0
	 */
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

	/**
	 * returns the average rating for an item.
	 *
	 * @param array $raters
	 * @return float rating for an item
	 * @since 1.0.0
	 */
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