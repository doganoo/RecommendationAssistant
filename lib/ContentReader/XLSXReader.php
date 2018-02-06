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
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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
	 * This method uses the PHPOffice/PHPSpreadsheet library to parse the ods document.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$dataDir = Application::getDataDirectory();
		$filePath = $dataDir . "/" . $file->getPath();
		$ods = new Xlsx();

		if (!is_file($filePath)) {
			Logger::warn("$filePath not found");
			return "";
		}
		try {
			if (!$ods->canRead($filePath)) {
				Logger::warn("can not read $filePath");
				return "";
			}

			$content = $ods->load($filePath);
			$sheets = $content->getAllSheets();
			$string = "";
			foreach ($sheets as $sheet) {
				$iterator = $sheet->getRowIterator();
				while ($iterator->valid()) {
					$row = $iterator->current();
					$cellIterator = $row->getCellIterator();
					while ($cellIterator->valid()) {
						$value = $cellIterator->current()->getValue();
						if ($value !== null) {
							$string .= " " . $value;
						}
						$cellIterator->next();
					}
					$iterator->next();
				}
			}
		} catch (Exception $exception) {
			Logger::error($exception->getMessage());
			return "";
		}
		return $string;
	}

}