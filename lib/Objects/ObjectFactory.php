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


use OCA\RecommendationAssistant\ContentReader\DocxReader;
use OCA\RecommendationAssistant\ContentReader\EmptyReader;
use OCA\RecommendationAssistant\ContentReader\HTMLReader;
use OCA\RecommendationAssistant\ContentReader\JSONReader;
use OCA\RecommendationAssistant\ContentReader\ODSReader;
use OCA\RecommendationAssistant\ContentReader\ODTReader;
use OCA\RecommendationAssistant\ContentReader\PDFReader;
use OCA\RecommendationAssistant\ContentReader\PPTXReader;
use OCA\RecommendationAssistant\ContentReader\RTFReader;
use OCA\RecommendationAssistant\ContentReader\TextfileReader;
use OCA\RecommendationAssistant\ContentReader\XLSXReader;
use OCA\RecommendationAssistant\ContentReader\XMLReader;
use OCA\RecommendationAssistant\Interfaces\IContentReader;

/**
 * Factory class in order to create objects and delegate the complexity of creation.
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class ObjectFactory {

	/**
	 * creates an object that has implemented the IContentReader interface in
	 * order to read content from a file. Actually the following mimetypes are
	 * supported:
	 *
	 * <li>TXT</li>
	 * <li>PDF</li>
	 * <li>HTML</li>
	 * <li>JSON</li>
	 * <li>XML</li>
	 * <li>DOCX</li>
	 * <li>RTF</li>
	 * <li>XLS</li>
	 * <li>ODT</li>
	 * <li>ODS</li>
	 *
	 * @param string $mimeType the mimetype of the file
	 * @since 1.0.0
	 * @return IContentReader object that reads file content
	 */
	public static function getContentReader(string $mimeType): IContentReader {
		if ($mimeType === IContentReader::TXT) {
			return new TextfileReader();
		} else if ($mimeType === IContentReader::PDF) {
			return new PDFReader();
		} else if ($mimeType === IContentReader::HTML) {
			return new HTMLReader();
		} else if ($mimeType === IContentReader::JSON) {
			return new JSONReader();
		} else if ($mimeType === IContentReader::XML) {
			return new XMLReader();
		} else if ($mimeType === IContentReader::DOCX) {
			return new DocxReader();
		} else if ($mimeType === IContentReader::RTF
			|| $mimeType === IContentReader::RTF_TEXT) {
			return new RTFReader();
		} else if ($mimeType === IContentReader::XLSX) {
			return new XLSXReader();
		} else if ($mimeType === IContentReader::PPTX) {
			return new PPTXReader();
		} else if ($mimeType === IContentReader::ODT) {
			return new ODTReader();
		} else if ($mimeType === IContentReader::ODS) {
			return new ODSReader();
		} else {
			return new EmptyReader();
		}
	}

}