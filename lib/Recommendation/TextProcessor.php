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

namespace OCA\RecommendationAssistant\Recommendation;


class TextProcessor {
	private $text;
	private $textArray;

	public function __construct(string $text) {
		$this->text = $text;
		//TODO valid solution?
		$this->text = mb_convert_encoding($this->text, "ASCII", mb_detect_encoding($this->text));
		$this->toArray();
	}

	private function toArray() {
		$this->textArray = str_word_count($this->text, 1);
		return $this->textArray;
	}

	public function removeNumeric() {
		$this->textArray = array_filter($this->textArray, function ($var) {
			return !is_numeric($var);
		});
	}

	public function removeDate() {
		$this->textArray = array_filter($this->textArray, function ($var) {
			return strtotime($var) === false;
		});
	}

	public function toLower() {
		array_walk($this->textArray, function (&$value, $key) {
			$value = strtolower($value);
		});
	}


	public function getTextAsArray() {
		return $this->textArray;
	}
}