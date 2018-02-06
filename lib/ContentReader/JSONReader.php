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
 * ContentReader class that is responsible for JSON documents.
 * Class implements IContentReader interface.
 *
 * @package OCA\RecommendationAssistant\ContentReader
 * @since 1.0.0
 */
class JSONReader implements IContentReader {

	/**
	 * Method implementation that is declared in the interface IContentReader.
	 * This method decodes a JSON string and passes returns a string with all
	 * entries.
	 *
	 * @param File $file the file whose content is to be read
	 * @since 1.0.0
	 * @return string the file content
	 */
	public function read(File $file): string {
		$string = "";
		try {
			$array = json_decode($file->getContent(), true);
			array_walk_recursive($array, function ($value, $key) use (&$string) {
				$string .= " " . $value;
			});
		} catch (NotPermittedException $e) {
			Logger::error($e->getMessage());
			return "";
		}
		return $string;
	}
}