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

use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;
use doganoo\PHPAlgorithms\Datastructure\Graph\Tree\BinarySearchTree;
use doganoo\PHPAlgorithms\Datastructure\Maps\HashMap;
use doganoo\PHPUtil\Util\NumberUtil;
use OCA\RecommendationAssistant\Util\Util;

/**
 * Getter/Setter class that represents an item for similarity calculation
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since   1.0.0
 */
class Item implements IComparable, \JsonSerializable {
	/**
	 * @var int $id
	 */
	private $id = -1;
	/**
	 * @var string $name
	 */
	private $name;
	/**
	 * @var BinarySearchTree $raters
	 */
	private $raters;
	/**
	 * @var string $oid
	 */
	private $oid;
	private $similarityMap;

	public function __construct() {
		$this->raters = new BinarySearchTree();
		$this->similarityMap = new HashMap();
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
	 *
	 * @since 1.0.0
	 */
	public function setName(string $name) {
		$this->name = $name;
	}

	/**
	 * Returns the owner of the item
	 *
	 * @return string the user that is the owner
	 * @since 1.0.0
	 */
	public function getOwnerId(): string {
		return $this->oid;
	}

	/**
	 * sets the owner of the item
	 *
	 * @param string $oid the owner of the item
	 *
	 * @since 1.0.0
	 */
	public function setOwnerId(string $oid) {
		$this->oid = $oid;
	}

	/**
	 * Returns a single rater that has rated the item
	 *
	 * @param string $uid
	 *
	 * @return Rater rater that has rated the item
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidSearchComparisionException
	 * @since 1.0.0
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	public function getRaterById(string $uid): ?Rater {
		$r = Util::toRater($uid, 0.0);
		if (!$this->raterPresent($r)) return null;
		$node = $this->raters->search($r);
		$value = $node->getValue();
		if ($value instanceof Rater) return $value;
		return null;
	}

	/**
	 * checks whether a user is present in the raters list
	 *
	 * @param string $uid the user id that should be checked
	 *
	 * @return bool whether the user is present
	 * @since 1.0.0
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidSearchComparisionException
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	public function raterPresent(string $uid): bool {
		$r = Util::toRater($uid, 0.0);
		if (null === $this->raters) return false;
		$node = $this->raters->search($r);
		if (null === $node) return false;
		/** @var Rater $rater */
		$rater = $node->getValue();
		return $rater->getUserId() === $r->getUserId();
	}

	/**
	 * determines whether an other instance of Item equals to this
	 * item or not.
	 * This function compares the keywords and the item names.
	 *
	 * @param Item $item item that should be compared
	 *
	 * @return bool whether the instance is the same or not
	 * @since 1.0.0
	 */
	public function equals(Item $item) {
		return $this->getId() === $item->getId();
	}

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
	 *
	 * @since 1.0.0
	 */
	public function setId(int $id) {
		$this->id = $id;
	}

	/**
	 * determines whether the instance is valid or not.
	 * The instance has to contain a owner object and a
	 * integer id in order to be valid.
	 *
	 * @return bool
	 */
	public function isValid(): bool {
		$hasOwner = $this->oid !== null && $this->oid !== "";
		$hasId = $this->id !== -1;
		return $hasOwner && $hasId;
	}

	/**
	 * adds a rater to the raters list. Old rating will be overwritten if
	 * the rater is already present in the list
	 *
	 * @param Rater $rater the rater that should be added to the ist
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function addRater(Rater $rater): bool {
		return $this->raters->insertValue($rater);
	}

	/**
	 * @param Item $item
	 * @param float $value
	 *
	 * @return bool
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException
	 */
	public function addSimiliarity(Item $item, float $value): bool {
		if (NumberUtil::compareFloat(0, $value)) return false;
		return $this->similarityMap->add($item->getId(), $value);
	}

	/**
	 * @return BinarySearchTree
	 */
	public function getRaters(): BinarySearchTree {
		return $this->raters;
	}

	public function similarItems(): array {
		return $this->similarityMap->keySet();
	}

	public function similars() {
		return $this->similarityMap;
	}

	public function similartyById($id) {
		$node = $this->similarityMap->get($id);
		return $node;
	}

	public function __toString() {
		return "" . $this->id;
	}

	/**
	 * @param $object
	 *
	 * @return int
	 */
	public function compareTo($object): int {
		if ($object instanceof Item) {
			if ($this->getId() === $object->getId()) return 0;
			if ($this->getId() > $object->getId()) return 1;
			if ($this->getId() < $object->getId()) return -1;
		}
		return -1;
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return [
			"id" => $this->id
			,
			"name" => $this->name
			,
			"oid" => $this->oid
			,
			"raters" => \json_encode($this->raters)
			,
			"similarity" => \json_encode($this->similarityMap),
		];
	}
}