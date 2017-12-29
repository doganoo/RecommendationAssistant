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

namespace OCA\RecommendationAssistant\Db;

use OCA\RecommendationAssistant\Objects\Logger;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\IDBConnection;

/**
 * Class that servers as a interface to the datastorage about files that are
 * already processed by RecommendationAssistant
 *
 * @package OCA\RecommendationAssistant\Db
 * @since 1.0.0
 */
class ProcessedFilesManager {
	/**
	 * @var IDBConnection $dbConnection
	 */
	private $dbConnection = null;

	/**
	 * Class constructor gets an instance of IDBConnection injected
	 *
	 * @param IDBConnection $dbConnection
	 * @since 1.0.0
	 */
	public function __construct(IDBConnection $dbConnection) {
		$this->dbConnection = $dbConnection;
	}

	/**
	 * inserts information about a single file in the database
	 *
	 * @param File $file file whose information should be stored
	 * @return bool whether the insertation was successfull or not
	 * @since 1.0.0
	 */
	public function insertFile(File $file): bool {
		if ($this->isPresentable($file)) {
			return true;
		}
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->insert(DbConstants::TABLE_NAME_FILES_PROCESSED)->values(
				[
					DbConstants::TB_FP_FILE_ID => $query->createNamedParameter($file->getId()),
					DbConstants::TB_FP_CREATION_TS => $query->createNamedParameter(time())
				]
			);
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			return false;
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
			return false;
		}
		$query->execute();
		$lastInsertId = $query->getLastInsertId();
		return is_int($lastInsertId);
	}

	/**
	 * inserts multiple files into the database. The method calls the
	 * insertFile() method to insert the information.
	 * The array elements have to be an instance of File otherwise they will
	 * be ignored.
	 *
	 * @param array $fileArray files that should be stored into the database
	 * @since 1.0.0
	 */
	public function insertFiles(array $fileArray) {
		foreach ($fileArray as $file) {
			if (!$file instanceof File) {
				continue;
			}
			$this->insertFile($file);
		}
	}

	/**
	 * checks whether a file is already inserted or not
	 *
	 * @param File $file file that should be checked
	 * @return bool whether the file inserted or not
	 * @since 1.0.0
	 */
	public function isPresentable(File $file) {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->select(DbConstants::TB_FP_FILE_ID)
				->from(DbConstants::TABLE_NAME_FILES_PROCESSED)
				->where($query->expr()->eq(DbConstants::TB_FP_FILE_ID, $query->createNamedParameter($file->getId())));
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			return false;
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
			return false;
		}

		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();
		return $row !== false;
	}

}