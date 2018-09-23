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

namespace OCA\RecommendationAssistant\Objects;

use doganoo\PHPAlgorithms\Datastructure\Stackqueue\Stack;

/**
 * Getter/Setter class that represents the recommendation
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since   1.0.0
 */
class Recommendation{
	/**
	 * @var string the user id
	 */
	private $userId;
	/**
	 * @var Stack $items
	 */
	private $items;

	public function __construct(){
		$this->items = new Stack();
	}

	/**
	 * returns the user id for whom the recommendation is made
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getUserId(): string{
		return $this->userId;
	}

	/**
	 * sets the user id for whom the recommendation is made
	 *
	 * @param string $userId
	 *
	 * @since 1.0.0
	 */
	public function setUserId(string $userId){
		$this->userId = $userId;
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function addRecommendation(Item $item){
		return $this->items->push($item);
	}

	public function size(): int{
		return $this->items->size();
	}

	public function getRecommendations(){
		return $this->items;
	}
}