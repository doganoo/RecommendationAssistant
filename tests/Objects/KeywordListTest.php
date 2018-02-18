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

namespace OCA\RecommendationAssistant\Tests\Objects;


use OCA\RecommendationAssistant\Objects\KeywordList;
use OCA\RecommendationAssistant\Recommendation\TextProcessor;
use OCA\RecommendationAssistant\Tests\TestCase;

class KeywordListTest extends TestCase {

	protected function setUp() {
		parent::setUp();
	}

	public function testFirst() {
		$content = $this->readAllFiles();
		$keywordList = new KeywordList();
		$count = 0;
		foreach ($content as $file) {
			$text = file_get_contents($file);
			$textProcessor = new TextProcessor($text);
			$list = $textProcessor->getKeywordList();
			$count += count($textProcessor->toArray());
			$keywordList->merge($list);
		}
		fwrite(STDOUT, "number of unique words: " . $this->formatNumber($count));
		fwrite(STDOUT, "\n");
		fwrite(STDOUT, "keywordList size: " . $this->formatNumber($keywordList->size()));
		$this->assertTrue($count >= $keywordList->size());
	}

	private function formatNumber($number) {
		//see: http://php.net/manual/en/function.number-format.php
		return number_format($number);
	}

	private function readAllFiles() {
		$files = [];
		if ($handle = opendir('text')) {

			while (false !== ($entry = readdir($handle))) {

				if ($entry != "." && $entry != "..") {
					$files[] = "text/$entry";
				}
			}
			closedir($handle);
		}
		return $files;
	}
}