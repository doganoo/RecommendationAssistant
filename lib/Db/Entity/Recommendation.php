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

namespace OCA\RecommendationAssistant\Db\Entity;


use OCP\AppFramework\Db\Entity;

/**
 * Recommendation class serves as an entity class that contains the recommendation
 * data stored in the database.
 *
 * @package OCA\RecommendationAssistant\Db\Entity
 * @since 1.0.0
 */
class Recommendation extends Entity implements \JsonSerializable {
	/**
	 * @var string $fileId the file id
	 */
	public $fileId;

	/**
	 * @var string $fileName the file name
	 */
	public $fileName;

	/**
	 *
	 * @var int $fileSize the file size
	 */
	public $fileSize;

	/**
	 * @var int $mTime the files modification time
	 */
	public $mTime;

	/**
	 * @var string the files extension
	 */
	public $extension;

	/**
	 * @var string $fileNameAndExtension the files name and extension
	 */
	public $fileNameAndExtension;

	/**
	 * @var string $etag
	 */
	public $etag;

	/**
	 * @var string $mimeType
	 */
	public $mimeType;

	/**
	 * @var string $path
	 */
	public $path;

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return [
			"fileId" => $this->fileId,
			"fileName" => $this->fileName,
			"mTime" => $this->mTime,
			"fileSize" => $this->fileSize,
			"extension" => $this->extension,
			"fileNameAndExtension" => $this->fileNameAndExtension,
			"mimeType" => $this->mimeType,
		];
	}
}