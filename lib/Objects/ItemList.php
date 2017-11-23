<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 02.11.17
 * Time: 17:20
 */

namespace OCA\DoganMachineLearning\Objects;

class ItemList implements \IteratorAggregate {
	private $itemList = array();

	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator() {
		return new \ArrayIterator($this->itemList);
	}

	public function get($index):?Item {
		if (!is_int($index)) {
			return null;
		}
		return $this->itemList[$index];
	}

	public function add(Item $item) {
		if (isset($this->itemList[$item->getId()])) {
			foreach ($item->getRaters() as $rater) {
				$this->itemList[$item->getId()]->addRater($rater);
			}
		} else {
			$this->itemList[$item->getId()] = $item;
		}
	}

	public function size(): int {
		return count($this->itemList);
	}
}