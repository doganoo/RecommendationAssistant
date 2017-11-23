<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 13.11.17
 * Time: 15:15
 */

namespace OCA\DoganMachineLearning\Service;


class TextProcessor {
	private $text;

	public function __construct(string $text) {
		$this->text = $text;
	}

	public function getTextAsArray() {
		$text = str_replace("\n", " ", $this->text);
		$text = str_replace("!", " ", $this->text);
		$text = str_replace("?", " ", $this->text);
		$text = str_replace(".", " ", $this->text);
		$text = str_replace(",", " ", $this->text);
		$textArray = explode(" ", $text);
		return $textArray;
	}
}