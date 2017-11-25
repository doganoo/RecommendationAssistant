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
 * Getter/Setter class that represents an item for similarity calculation
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class Item {
	/**
	 * @var int $id
	 */
	private $id;
	/**
	 * @var string $name
	 */
	private $name;
	/**
	 * @var array $keywords
	 */
	private $keywords;
	/**
	 * @var array $raters
	 */
	private $raters;

	/**
	 * Returns the id of the item
	 *
	 * @return int the id
	 * @since 1.0.0
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * sets the id of the item
	 *
	 * @param int $id the id associated to the item
	 * @since 1.0.0
	 */
	public function setId(int $id) {
		$this->id = $id;
	}

	/**
	 * Returns the name of the item
	 *
	 * @return string the name
	 * @since 1.0.0
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * sets the name of the item
	 *
	 * @param string $name the items name
	 * @since 1.0.0
	 */
	public function setName(string $name) {
		$this->name = $name;
	}

	/**
	 * Returns the keywords that describe the item
	 *
	 * @return array the keywords
	 * @since 1.0.0
	 */
	public function getKeywords(): array {
		if ($this->keywords == null) {
			return [];
		}
		return $this->keywords;
	}

	/**
	 * sets the keywords that describe the item
	 *
	 * @param array $keywords the items name
	 * @since 1.0.0
	 */
	public function setKeywords(array $keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * Returns a single rater that has rated the item
	 *
	 * @return Rater rater that has rated the item
	 * @since 1.0.0
	 */
	public function getRater(string $index): Rater {
		return $this->raters[$index];
	}

	/**
	 * Returns all raters that have rated the item
	 *
	 * @return array the raters
	 * @since 1.0.0
	 */
	public function getRaters(): array {
		if (is_null($this->raters)) {
			return [];
		}
		return $this->raters;
	}

	/**
	 * adds a rater to the raters list. Old rating will be overwritten if
	 * the rater is already present in the list
	 *
	 * @param Rater $rater the rater that should be added to the ist
	 * @since 1.0.0
	 */
	public function addRater(Rater $rater) {
		$this->raters[$rater->getUser()->getUID()] = $rater;
	}

	/**
	 * checks whether a user is present in the raters list
	 *
	 * @param string $uid the user id that should be checked
	 * @return bool whether the user is present
	 * @since 1.0.0
	 */
	public function raterPresent($uid): bool {
		return isset($this->raters[$uid]);
	}

	/**
	 * determines whether an other instance of Item equals to this
	 * item or not.
	 * This function compares the keywords and the item names.
	 *
	 * @param Item $item item that should be compared
	 * @return bool whether the instance is the same or not
	 * @since 1.0.0
	 */
	public function equals(Item $item) {
		$equalKeywords = ($this->getKeywords() == $item->getKeywords()) && $this->getKeywords() !== [] && $item->getKeywords() !== [];
		$equalNames = strcmp($this->getName(), $item->getName()) == 0;
		//TODO check the owner
		return $equalKeywords && $equalNames;
	}
	//TODO sizeOfRaters aus Klassendiagramm entferne

}