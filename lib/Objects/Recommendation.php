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


/**
 * Getter/Setter class that represents the recommendation
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class Recommendation {
	/**
	 *
	 * @var string the file owner
	 */
	private $ownerId;

	/**
	 *
	 * @var string the user id
	 */
	private $userId;

	/**
	 *
	 * @var string the file
	 */
	private $fileId;

	/**
	 * @var int $fileName the filename
	 */
	private $fileName;

	/**
	 * returns the id of the item owner
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getOwnerId(): string {
		return $this->ownerId;
	}

	/**
	 * sets the owner id of the item
	 *
	 * @param string $ownerId
	 * @since 1.0.0
	 */
	public function setOwnerId(string $ownerId) {
		$this->ownerId = $ownerId;
	}

	/**
	 * returns the user id for whom the recommendation is made
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getUserId(): string {
		return $this->userId;
	}

	/**
	 * sets the user id for whom the recommendation is made
	 *
	 * @param string $userId
	 * @since 1.0.0
	 */
	public function setUserId(string $userId) {
		$this->userId = $userId;
	}

	/**
	 * returns the file id that is recommended
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getFileId(): string {
		return $this->fileId;
	}

	/**
	 * sets the file id that is recommended
	 *
	 * @param string $fileId
	 * @since 1.0.0
	 */
	public function setFileId(string $fileId) {
		$this->fileId = $fileId;
	}

	/**
	 * @return int
	 * @since 1.0.0
	 */
	public function getFileName(): int {
		return $this->fileName;
	}

	/**
	 * @param int $fileName
	 * @since 1.0.0
	 */
	public function setFileName(int $fileName) {
		$this->fileName = $fileName;
	}
}