<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 02.11.17
 * Time: 17:17
 */

namespace OCA\DoganMachineLearning\Objects;
class Item {
	private $id;
	private $name;
	private $keywords;
	private $raters;

	public function getRaters(): array {
		if (is_null($this->raters)) {
			return [];
		}
		return $this->raters;
	}

	public function getRater(string $index): Rater {
		return $this->raters[$index];
	}


	public function raterPresent($uid): bool {
		return isset($this->raters[$uid]);
	}

	public function addRater(Rater $rater) {
		$this->raters[$rater->getUser()->getUID()] = $rater;
	}

	public function sizeOfRaters(): int {
		return count($this->raters);
	}

	/**
	 * @return mixed
	 */
	public function getKeywords(): array {
		if ($this->keywords == null) {
			return [];
		}
		return $this->keywords;
	}

	/**
	 * @param mixed $keywords
	 */
	public function setKeywords(array $keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * @return mixed
	 */
	public function getId(): int {
		return $this->id;
	}

	public function setId(int $id) {
		$this->id = $id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name) {
		$this->name = $name;
	}

	public function equals(Item $item) {
		$equalKeywords = ($this->getKeywords() == $item->getKeywords()) && $this->getKeywords() !== [] && $item->getKeywords() !== [];
		$equalNames = strcmp($this->getName(), $item->getName()) == 0;
//		$equalOwner = strcmp($this->getUser()->getUID(), $item->getUser()->getUID()) == 0;
//		return $equalKeywords && $equalNames && $equalOwner;
		return $equalKeywords && $equalNames;
	}
}