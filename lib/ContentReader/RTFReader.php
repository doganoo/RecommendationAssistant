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

class RTFReader implements IContentReader {

	/**
	 * reads the file content
	 *
	 * @param File $file the actual file
	 * @return string file content
	 * @since 1.0.0
	 */
	public function read(File $file): string {
		$parser = new \RtfReader();
		try {
			$parser->Parse($file->getContent());
		} catch (NotPermittedException $e) {
			Logger::error($e->getMessage());
			return "";
		}
		$formatter = new \RtfHtml();
		$text = $formatter->Format($parser->root);
		return strip_tags(str_replace('<', ' <', $text));
	}
}