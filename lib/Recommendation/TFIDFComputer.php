<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 06.11.17
 * Time: 11:03
 */

namespace OCA\DoganMachineLearning\Recommendation;

use OCA\DoganMachineLearning\Interfaces\IComputable;
use OCA\DoganMachineLearning\Objects\Item;
use OCA\DoganMachineLearning\Objects\ItemList;

class TFIDFComputer implements IComputable {
	private $item = null;
	private $itemBase = null;
	private $result = [];

	public function __construct(Item $item, ItemList $itemBase) {
		$this->resetItem($item);
		$this->resetItemBase($itemBase);
	}

	public function resetItem(Item $item) {
		$this->item = $item;
	}

	public function resetItemBase(ItemList $itemBase) {
		$this->itemBase = $itemBase;
	}

	public function compute() {
		$termFrequency = count($this->item->getKeywords());
		$itemBaseSize = $this->itemBase->size();
		foreach ($this->item->getKeywords() as $keyword) {
			if (trim($keyword) == "") {
				continue;
			}
			$count = $this->countKeywordInItemBase($keyword);
			if ($count == 0) {
				$count = 1;
			}
			$inverseDocumentFrequency = log($itemBaseSize / $count);
			$tfIdf = $termFrequency * $inverseDocumentFrequency;
			$this->result[$keyword] = $tfIdf;
		}
		$this->remove();
		return $this->result;
	}

	private function remove() {
		$resultSize = count($this->result);
		$x = $resultSize / 3;
		$x = round($x, PHP_ROUND_HALF_UP);
		$this->result = array_slice($this->result, 0, $resultSize - $x, true);
	}

	private function countKeywordInItemBase(string $keyword) {
		$i = 0;
		foreach ($this->itemBase as $item) {
			$keywords = $item->getKeywords();
			if (array_key_exists($keyword, $keywords)) {
				$i++;
			}
		}
		return $i;
	}
}