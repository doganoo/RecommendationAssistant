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
use OCP\Files\File;

/**
 * ContentReader class that is responsible for MS Excel .xlsx documents.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class XLSXReader implements IContentReader {

	/**
	 * Method implementation that is declared in the interface IContentReader.
	 * This method unzips the .xlsx MS Excel document and reads
	 * the XML documents within the zip located at xl/worksheets/slide<number>.xml.
	 * Each PPTX slide has its own xml file and therefore, the method iterates
	 * over each XML file that is located in xl/worksheets. The XML file content
	 * is parsed with the PHP internal \DOMDocument class.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$dataDir = Application::getDataDirectory();
		$filePath = $dataDir . "/" . $file->getPath();
		$zipPath = $dataDir . "/" . $file->getId();
		if (!is_file($filePath)) {
			return "";
		}

		$archive = new \ZipArchive();
		$opened = $archive->open($filePath);
		$textContent = "";
		if (true === $opened) {
			$archive->extractTo($zipPath);
			if ($handle = opendir($zipPath . "/xl/worksheets/")) {
				$entry = readdir($handle);
				$i = 1;
				while (false !== ($entry = readdir($handle))) {
					$pathInfo = pathinfo($entry);
					if ($pathInfo["extension"] === "xml") {
						$contentDocument = $zipPath . "/xl/worksheets/sheet$i.xml";
						$content = file_get_contents($contentDocument);
						$dom = new \DOMDocument();
						$dom->loadXML($content);
						$textContent .= $dom->textContent;
						$i++;
					}
				}
			}
		}
		if (is_dir($zipPath)) {
			system("rm -rf " . escapeshellarg($zipPath));
		}
		return $textContent;
	}

}