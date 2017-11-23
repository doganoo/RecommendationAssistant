<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 06.11.17
 * Time: 10:46
 */

namespace OCA\DoganMachineLearning\Recommendation;

use OCA\DoganMachineLearning\Interfaces\IComputable;
use OCA\DoganMachineLearning\Objects\Item;
use OCA\DoganMachineLearning\Objects\ItemList;

class Sport1Computer implements IComputable {
	private $item = null;
	private $item1 = null;
	private $tfIdfComputer = null;
	private $itemBase = null;

	//TODO do not inject itembase!
	public function __construct(Item $item, Item $item1, ItemList $itemBase) {
		$this->item = $item;
		$this->item1 = $item1;
		$this->itemBase = $itemBase;
	}

	public function compute() {
		$result = $this->computeTfIdf($this->item);
		$result1 = $this->computeTfIdf($this->item1);
		return $this->doCompute($result, $result1);
	}

	private function computeTfIdf(Item $item) {
		$this->tfIdfComputer = new TFIDFComputer($item, $this->itemBase);
		$result = $this->tfIdfComputer->compute();
		return $result;
	}

	private function doCompute($result, $result1) {
		$upper = 0;
		$lower1 = 0;
		$lower2 = 0;
		foreach ($result as $keyword => $value) {
			$val = 0;
			if (isset($result1[$keyword])) {
				$val = $result1[$keyword];
			}
			$upper += $value * $val;
			$lower1 += pow($value, 2);
		}
		foreach ($result1 as $keyword => $value) {
			$lower2 += pow($value, 2);
		}
		$sqrt = sqrt($lower1 * $lower2);
		if ($sqrt == 0) {
			return 0;
		}
		return $upper / $sqrt;
	}
}