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

use OCA\Files_External\NotFoundException;
use OCA\RecommendationAssistant\Objects\ConsoleLogger;
use OCA\RecommendationAssistant\Objects\Logger;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
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
	 * deletes all weights for a given group id
	 *
	 * @param string $groupId the group id for that the weights should be deleted
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
			return false;
		} catch (\OCP\Files\NotFoundException $e) {
			Logger::error($e->getMessage());
			return false;
		}
		$query->execute();
		$lastInsertId = $query->getLastInsertId();
		return is_int($lastInsertId);
	}

	public function handle(File $file, string $type) {
		$presentable = $this->isPresentable($file, $type);
		if ($presentable) {
			$this->deleteFile($file, $type);
		}
		$this->insertFile($file, $type);
	}

	public function queryChangeTs(File $file, string $type): int {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->select(DbConstants::TB_CFL_CHANGE_TS)
				->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
				->where($query->expr()->eq(DbConstants::TB_CFL_FILE_ID, $query->createNamedParameter($file->getId())))
				->where($query->expr()->eq(DbConstants::TB_CFL_TYPE, $query->createNamedParameter($type)));
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			return -1;
		} catch (\OCP\Files\NotFoundException $e) {
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
		} catch (\OCP\Files\NotFoundException $e) {
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