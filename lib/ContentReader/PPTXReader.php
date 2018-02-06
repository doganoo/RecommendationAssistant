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
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\RichText;

/**
 * ContentReader class that is responsible for MS PowerPoint .pptx documents.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class PPTXReader implements IContentReader {

	/**
	 * Method implementation that is declared in the interface IContentReader.
	 * This method unzips the .pptx MS Power Point document and reads
	 * the XML documents within the zip located at ppt/slides/slide<number>.xml.
	 * Each PPTX slide has its own xml file and therefore, the method iterates
	 * over each XML file that is located in ppt/slides. The XML file content
	 * is parsed with the PHP internal \DOMDocument class.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$dataDir = Application::getDataDirectory();
		$filePath = $dataDir . "/" . $file->getPath();

		if (!is_file($filePath)) {
			Logger::warn("$filePath not found");
			return "";
		}
		$oReader = IOFactory::createReader('PowerPoint2007');
		if (!$oReader->canRead($filePath)) {
			Logger::warn("can not read $filePath");
			return "";
		}

		try {
			/** @var PhpPresentation $reader */
			$reader = $oReader->load($filePath);
			$string = "";
			foreach ($reader->getAllSlides() as $slide) {
				$iterator = $slide->getShapeCollection()->getIterator();
				while ($iterator->valid()) {
					/** @var RichText $current */
					$current = $iterator->current();
					$plainText = $current->getPlainText();
					if ($plainText !== "") {
						$string = $string . " " . $plainText;
					}
					$iterator->next();
				}
			}
		} catch (\Exception $exception) {
			Logger::error($exception->getMessage());
			return "";
		}
		return $string;
	}

}