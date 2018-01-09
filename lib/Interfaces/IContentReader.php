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

namespace OCA\RecommendationAssistant\Interfaces;

use OCP\Files\File;

/**
 * Interface IContentReader implemented by all objects
 * that reads file content.
 *
 * @package OCA\RecommendationAssistant\Interfaces
 * @since 1.0.0
 */
interface IContentReader {
	/**
	 * @const TXT mime type for plain text files
	 */
	const TXT = "text/plain";

	/**
	 * @const HTML mime type for HTML files
	 */
	const HTML = "text/html";

	/**
	 * @const PDF mime type for PDF files
	 */
	const PDF = "application/pdf";

	/**
	 * @const JSON mime type for JSON files
	 */
	const JSON = "application/json";

	/**
	 * @const XML mime type for XML files
	 */
	const XML = "application/xml";

	/**
	 * @const DOCX mime type for MS Word docx documents
	 */
	const DOCX = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";

	/**
	 * @const RTF mime type for RTF files
	 */
	const RTF = "application/rtf";

	/**
	 * @const RTF_TEXT mime type for RTF files
	 */
	const RTF_TEXT = "text/rtf";

	/**
	 * @const XLSX mime type for MS Excel xlsx files
	 */
	const XLSX = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

	/**
	 * @const PPTX mime type for MS PowerPoint pptx files
	 */
	const PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

	/**
	 * @const ODT mime type for OpenOffice odt files
	 */
	const ODT = 'application/vnd.oasis.opendocument.text';

	/**
	 * @const ODS mime type for OpenOffice ods files
	 */
	const ODS = 'application/vnd.oasis.opendocument.spreadsheet';

	/**
	 * reads the file content
	 *
	 * @param File $file the actual file
	 * @return string file content
	 * @since 1.0.0
	 */
	public function read(File $file): string;
}