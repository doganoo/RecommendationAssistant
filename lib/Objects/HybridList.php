<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 07.12.17
 * Time: 14:49
 */

namespace OCA\RecommendationAssistant\Objects;


use OCP\IUser;
use Traversable;

class HybridList implements \IteratorAggregate {
	private $hybridList = [];

	public function getHybridByUser(Item $item, IUser $user) {
		if (isset($this->hybridList[$user->getUID()][$item->getId()])) {
			return $this->hybridList[$user->getUID()][$item->getId()];
		} else {
			return new HybridItem();
		}
	}

	public function add(HybridItem $hybrid, IUser $user, Item $item) {
		$this->hybridList[$user->getUID()][$item->getId()] = $hybrid;
	}


	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator() {
		return new \ArrayIterator($this->hybridList);
	}
}