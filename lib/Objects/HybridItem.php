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


use OCP\IUser;

class HybridItem {
	private $collaborative = 0;
	private $contentBased = 0;
	private $item = null;
	private $user = null;

	/**
	 * @param Item $item
	 */
	public function setItem(Item $item) {
		$this->item = $item;
	}

	/**
	 * @param IUser $user
	 */
	public function setUser(IUser $user) {
		$this->user = $user;
	}

	public function getCollaborative(): float {
		return $this->collaborative;
	}

	public function setCollaborative(float $collaborative) {
		$this->collaborative = $collaborative;
	}

	public function getContentBased(): float {
		return $this->contentBased;
	}

	public function setContentBased(float $contentBased) {
		$this->contentBased = $contentBased;
	}

	public function __toString() {
		$val = HybridItem::weightedAverage($this);
		return "[#itemId#][#{$this->item->getId()}#][#itemName#][#{$this->item->getName()}#][#userId#][#{$this->user->getUID()}#][#collaborative#][#{$this->collaborative}#][#contentBased#][#{$this->contentBased}#][#recommendation#][$val]";
	}

	public static function weightedAverage(HybridItem $hybrid) {
		$contentBased = 0.9;
		$collaborative = 1 - $contentBased;
		return ($contentBased * $hybrid->getContentBased()) + ($collaborative * $hybrid->getCollaborative());
	}
}
