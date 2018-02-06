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
use OCA\RecommendationAssistant\Log\Logger;
use OCP\Files\File;
use OCP\Files\NotPermittedException;

/**
 * ContentReader class that is responsible for XML .xml files.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class XMLReader implements IContentReader {

	/**
	 * Method implementation that is declared in the interface IContentReader.
	 * This method removes all tags of the content and returns it.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		try {
			return strip_tags(str_replace('<', ' <', $file->getContent()));
		} catch (NotPermittedException $e) {
			Logger::error($e->getMessage());
			return "";
		}

	}
}