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

use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Log\Logger;
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
	 * @param string $type the type of being processed. Possible types:
	 *
	 * <ul>recommendation</ul>
	 * <ul>userprofile</ul>
	 *
	 * @return bool whether the insertation was successful or not
	 * @since 1.0.0
	 */
	public function insertFile(File $file, string $type): bool {
		if (Application::DEBUG) {
			return true;
		}
		if ($this->isPresentable($file, $type)) {
			return true;
		}
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->insert(DbConstants::TABLE_NAME_FILES_PROCESSED)->values(
				[
					DbConstants::TB_FP_FILE_ID => $query->createNamedParameter($file->getId()),
					DbConstants::TB_FP_CREATION_TS => $query->createNamedParameter(time()),
					DbConstants::TB_FP_TYPE => $query->createNamedParameter($type),
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
	 * checks whether a file is already inserted or not
	 *
	 * @param File $file file that should be checked
	 * @param string $type the type of being processed. Possible types:
	 *
	 * <ul>recommendation</ul>
	 * <ul>userprofile</ul>
	 *
	 * @return bool whether the file inserted or not
	 * @since 1.0.0
	 */
	public function isPresentable(File $file, string $type) {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->select(DbConstants::TB_FP_FILE_ID)
				->from(DbConstants::TABLE_NAME_FILES_PROCESSED)
				->where($query->expr()->eq(DbConstants::TB_FP_FILE_ID, $query->createNamedParameter($file->getId())))
				->andWhere($query->expr()->eq(DbConstants::TB_FP_TYPE, $query->createNamedParameter($type)));
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

	/**
	 * deletes a file from the database
	 *
	 * @param File $file the file that should be deleted
	 * @param string $type the type of being processed. Possible types:
	 *
	 * <ul>recommendation</ul>
	 * <ul>userprofile</ul>
	 *
	 * @since 1.0.0
	 */
	public function deleteFile(File $file, string $type) {
		try {
			$query = $this->dbConnection->getQueryBuilder();
			$query->delete(DbConstants::TABLE_NAME_FILES_PROCESSED)
				->where($query->expr()->eq(DbConstants::TB_FP_FILE_ID, $query->createNamedParameter($file->getId())))
				->andWhere($query->expr()->eq(DbConstants::TB_FP_TYPE, $query->createNamedParameter($type)))
				->execute();
			return true;
		} catch (InvalidPathException $exception) {
			Logger::error($exception->getMessage());
			return false;
		} catch (NotFoundException $exception) {
			Logger::error($exception->getMessage());
			return false;
		}
		return false;

	}

}