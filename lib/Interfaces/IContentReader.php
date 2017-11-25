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
	public const TXT = "text/plain";
	public const HTML = "text/html";
	public const PDF = "application/pdf";
	public const JSON = "application/json";
	public const XML = "application/xml";
	public const DOC = "application/msword";
	public const DOCX = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
	public const RTF = "application/rtf";
	public const XLS = "application/vnd.ms-excel";
	public const PPT = 'application/vnd.ms-powerpoint';
	public const ODT = 'application/vnd.oasis.opendocument.text';
	public const ODS = 'application/vnd.oasis.opendocument.spreadsheet';

	/**
	 * reads the file content
	 *
	 * @param File $file the actual file
	 * @return string file content
	 * @since 1.0.0
	 */
	public function read(File $file): string;
}