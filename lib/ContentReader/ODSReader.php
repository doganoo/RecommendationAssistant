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
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;

/**
 * ContentReader class that is responsible for Open Office .ods spreadsheet documents.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class ODSReader implements IContentReader {

	/**
	 * Method implementation that is declared in the interface IContentReader.
	 * This method unzips the .ods Open Office Spreadsheet document and reads
	 * the XML document within the zip located at content.xml. The XML document
	 * is parsed with the PHP internal \DOMDocument class.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$dataDir = Application::getDataDirectory();
		$filePath = $dataDir . "/" . $file->getPath();
		try {
			$zipPath = $dataDir . "/" . $file->getId();
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			return "";
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
			return "";
		}
		if (!is_file($filePath)) {
			return "";
		}

		$archive = new \ZipArchive();
		$opened = $archive->open($filePath);
		$textContent = "";
		if (true === $opened) {
			$archive->extractTo($zipPath);
			$contentDocument = $zipPath . "/content.xml";
			$content = file_get_contents($contentDocument);
			$dom = new \DOMDocument();
			$dom->loadXML($content);
			$textContent = $dom->textContent;
		}
		if (is_dir($zipPath)) {
			system("rm -rf " . escapeshellarg($zipPath));
		}
		return $textContent;
	}
}