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

use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Log\Logger;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\IDBConnection;

/**
 * Class that servers as a interface to the datastorage that the latest changes
 * of a file.
 *
 * @package OCA\RecommendationAssistant\Db
 * @since 1.0.0
 */
class ChangedFilesManager {
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
	 * deletes a single file from the database
	 *
	 * @param File $file the file that should be deleted
	 * @param string $type the type of changed files
	 * @since 1.0.0
	 */
	public function deleteFile(File $file, string $type) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->delete(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
			->where($query->expr()->eq(DbConstants::TB_CFL_FILE_ID, $query->createNamedParameter($file->getId())))
			->andWhere($query->expr()->eq(DbConstants::TB_CFL_TYPE, $query->createNamedParameter($type)))
			->execute();
	}

	/**
	 * inserts information about a single file in the database
	 *
	 * @param File $file file whose information should be stored
	 * @param string $type row type
	 * @return bool whether the insertation was successfull or not
	 * @since 1.0.0
	 */
	public function insertFile(File $file, string $type): bool {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->insert(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)->values(
				[
					DbConstants::TB_CFL_FILE_ID => $query->createNamedParameter($file->getId()),
					DbConstants::TB_CFL_CHANGE_TS => $query->createNamedParameter(time()),
					DbConstants::TB_CFL_CREATION_TS => $query->createNamedParameter(time()),
					DbConstants::TB_CFL_TYPE => $query->createNamedParameter($type)
				]
			);
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			ConsoleLogger::error($e->getMessage());
			return false;
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
			ConsoleLogger::error($e->getMessage());
			return false;
		}
		$query->execute();
		$lastInsertId = $query->getLastInsertId();
		return is_int($lastInsertId);
	}

	/**
	 * This method deletes first a file from the database if it is already inserted.
	 * Then, the method calls a method to insert the file to the database.
	 *
	 * @param File $file
	 * @param string $type
	 * @since 1.0.0
	 */
	public function deleteBeforeInsert(File $file, string $type) {
		$presentable = $this->isPresentable($file, $type);
		if ($presentable) {
			$this->deleteFile($file, $type);
		}
		$this->insertFile($file, $type);
	}

	/**
	 * returns the last changed timestamp for a given file/type.
	 *
	 * @param File $file the file for that the query should be executed
	 * @param string $type the type of the query
	 * @return int
	 * @since 1.0.0
	 */
	public function queryChangeTs(File $file, string $type): int {
		$query = $this->dbConnection->getQueryBuilder();
		$changeTs = 0;
		try {
			$query->select(DbConstants::TB_CFL_CHANGE_TS)
				->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
				->where($query->expr()->eq(DbConstants::TB_CFL_FILE_ID, $query->createNamedParameter($file->getId())))
				->where($query->expr()->eq(DbConstants::TB_CFL_TYPE, $query->createNamedParameter($type)));
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			return -1;
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
			return -1;
		}

		$result = $query->execute();
		while (false !== $row = $result->fetch()) {
			$changeTs = $row[DbConstants::TB_CFL_CHANGE_TS];
		}
		$result->closeCursor();
		return intval($changeTs);
	}

	/**
	 * checks whether a file is already inserted or not
	 *
	 * @param File $file file that should be checked
	 * @param string $type the files type
	 * @return bool whether the file inserted or not
	 * @since 1.0.0
	 */
	public function isPresentable(File $file, string $type) {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->select(DbConstants::TB_CFL_FILE_ID)
				->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
				->where($query->expr()->eq(DbConstants::TB_CFL_FILE_ID, $query->createNamedParameter($file->getId())))
				->andWhere($query->expr()->eq(DbConstants::TB_CFL_TYPE, $query->createNamedParameter($type)));
		} catch (InvalidPathException $e) {
			ConsoleLogger::debug($e->getMessage());
			Logger::error($e->getMessage());
			return false;
		} catch (NotFoundException $e) {
			ConsoleLogger::debug($e->getMessage());
			Logger::error($e->getMessage());
			return false;
		}

		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();
		return $row !== false;
	}
}