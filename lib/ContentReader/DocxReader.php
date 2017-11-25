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


use OCA\RecommendationAssistant\Interfaces\IContentReader;
use OCP\Files\File;

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
	 * This method unzips the .docx MS Word document and reads the XML document
	 * within the zip located at word/document.xml. The XML document is parsed with
	 * the PHP internal \DOMDocument class.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$dataDir = \OC::$server->getConfig()->getSystemValue('datadirectory', '');
		$filePath = $dataDir . "/" . $file->getPath();
		if (!is_file($filePath)) {
			return "";
		}

		if (is_dir($file->getId())) {
			system("rm -rf " . $file->getId());
		}

		$archive = new \ZipArchive();
		$opened = $archive->open($filePath);
		$textContent = "";
		if (true === $opened) {
			$archive->extractTo($file->getId());
			$contentDocument = $file->getId() . "/word/document.xml";
			$content = file_get_contents($contentDocument);
			$dom = new \DOMDocument();
			$dom->loadXML($content);
			$textContent = $dom->textContent;
		}
		return $textContent;
	}

}