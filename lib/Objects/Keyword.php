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


class Keyword {
	/**
	 * @var string $keyword
	 */
	private $keyword;

	/**
	 * @var double $tfIdf
	 */
	private $tfIdf;

	/**
	 * @return string
	 */
	public function getKeyword(): string {
		return $this->keyword;
	}

	/**
	 * @param string $keyword
	 */
	public function setKeyword(string $keyword) {
		$this->keyword = $keyword;
	}

	/**
	 * @return float
	 */
	public function getTfIdf(): float {
		return floatval($this->tfIdf);
	}

	/**
	 * @param float $tfIdf
	 */
	public function setTfIdf(float $tfIdf) {
		$this->tfIdf = $tfIdf;
	}


}