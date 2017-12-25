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

namespace OCA\RecommendationAssistant\Objects;

/**
 * Class that serves as a list for all items for that the similarity is measured.
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class ItemToItemMatrix {
	/**
	 * @var array $matrix the matrix that contains all items
	 */
	private $matrix = [];

	/**
	 * adds two items and the corresponding similarity value to the matrix.
	 *
	 * @param Item $item the first item
	 * @param Item $item1 the second item
	 * @param float $similarity the similarity value between $item and $item1
	 * @since 1.0.0
	 */
	public function add(Item $item, Item $item1, float $similarity) {
		$index = $item->getId();
		$index1 = $item1->getId();
		$array = [];
		if (isset($this->matrix[$index])) {
			$array = $this->matrix[$index];
		}
		$array[$index1] = $similarity;
		$this->matrix[$index] = $array;
	}

	/**
	 * returns the similarity value for $item and $item1
	 *
	 * @param Item $item the first item
	 * @param Item $item1 the second item
	 * @return int
	 */
	public function get(Item $item, Item $item1) {
		if (!isset($this->matrix[$item->getId()])) {
			return 0;
		}
		$arr = $this->matrix[$item->getId()];
		if (!isset($arr[$item1->getId()])) {
			return 0;
		}
		return $arr[$item1->getId()];
	}

}