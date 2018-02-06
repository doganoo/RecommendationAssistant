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

namespace OCA\RecommendationAssistant\ContentReader;


use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Interfaces\IContentReader;
use OCA\RecommendationAssistant\Log\Logger;
use OCP\Files\File;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Reader\Word2007;

/**
 * ContentReader class that is responsible for Microsoft Word .docx documents.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class DocxReader implements IContentReader {

	/**
	 * Method implementation that is declared in the interface IContentReader.
	 * This method uses the PHPOffice/PHPWord library to parse the word document.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$dataDir = Application::getDataDirectory();
		$filePath = $dataDir . "/" . $file->getPath();
		$word = new Word2007();

		if (!is_file($filePath)) {
			Logger::warn("$filePath not found");
			return "";
		}
		if (!$word->canRead($filePath)) {
			Logger::warn("can not read $filePath");
			return "";
		}

		$reader = $word->load($filePath);
		$sections = $reader->getSections();

		$string = "";
		foreach ($sections as $section) {
			foreach ($section->getElements() as $element) {
				if ($element instanceof Text) {
					if (null !== $element->getText()) {
						$string .= $element->getText();
					}
				}
			}
		}
		return $string;
	}
}