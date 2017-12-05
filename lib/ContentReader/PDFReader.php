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

use OCP\Files\File;
use Smalot\PdfParser\Parser;
use OCA\RecommendationAssistant\Interfaces\IContentReader;

/**
 * ContentReader class that is responsible for Portable Document Format pdf documents.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class PDFReader implements IContentReader {
	/**
	 * @var Smalot\PdfParser\Parser $parser
	 */
	private $parser = null;

	/**
	 * Class constructor creates the parser object that reads the PDF file
	 * content.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->parser = new Parser();
	}

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
		$content = $this->parser->parseContent($file->getContent());
		return $content->getText();
	}
}