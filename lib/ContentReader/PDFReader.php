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

use chv\pdftotext\PdfToText;
use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Interfaces\IContentReader;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Log\Logger;
use OCA\RecommendationAssistant\Util\Util;
use OCP\Files\File;

/**
 * ContentReader class that is responsible for Portable Document Format pdf documents.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class PDFReader implements IContentReader {

	/**
	 * Method implementation that is declared in the interface IContentReader.
	 * This method uses the Smalot\PdfParser\Parser object in order to read
	 * the PDF file content and returns it.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$dataDir = Application::getDataDirectory();
		$filePath = $dataDir . "/" . $file->getPath();
		$text = "";

		if (!Util::isFile($filePath)) {
			return "";
		}

		try {
			$pdf = new PdfToText($filePath);
			$text = $pdf->Text;
		} catch (\Exception $exception) {
			Logger::error($exception->getTraceAsString());
			ConsoleLogger::error($exception->getTraceAsString());
		}
		/**
		 * Strip non-ASCII characters from a String
		 *
		 * see http://hawkee.com/snippet/4224/
		 * last visit: 02.02.18
		 */
		$text = preg_replace('/[^(\x20-\x7F)]*/', '', $text);
		return $text;
	}
}