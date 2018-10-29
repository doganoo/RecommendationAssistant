<?php
/**
 * @copyright Copyright (c) 2017, Dogan Ucar (dogan@dogan-ucar.de)
 * @license   GNU AGPL version 3 or any later version
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\RecommendationAssistant\Db;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Log\Logger;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Util\NodeUtil;
use OCA\RecommendationAssistant\Util\Util;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\IDBConnection;

/**
 * Class that servers as a interface to the datastorage that the latest changes
 * of a file.
 *
 * @package OCA\RecommendationAssistant\Db
 * @since   1.0.0
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
	 *
	 * @since 1.0.0
	 */
	public function __construct(IDBConnection $dbConnection) {
		$this->dbConnection = $dbConnection;
	}

	public function deleteOutdated() {
		$limit = 180 * 60 * 60 * 24;
		$now = \time() - $limit;
		$query = $this->dbConnection->getQueryBuilder();
		$query->delete()->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
			->where($query->expr()->lt(DbConstants::TB_CFL_CREATION_TS, $query->createNamedParameter($now - $limit)));
		$query->execute();
	}

	/**
	 * @return ArrayList
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	public function allFiles(): ArrayList {
		$list = new ArrayList();
		$query = $this->dbConnection->getQueryBuilder();
		$query->select(
			DbConstants::TB_CFL_FILE_ID
			, DbConstants::TB_CFL_USER_ID
			, DbConstants::TB_CFL_CREATION_TS
		)
			->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG);
		$result = $query->execute();
		while (false !== $row = $result->fetch()) {
			$fileId = $row[DbConstants::TB_CFL_FILE_ID];
			$userId = $row[DbConstants::TB_CFL_USER_ID];
			$createTs = $row[DbConstants::TB_CFL_CREATION_TS];
			$file = NodeUtil::getFile(
				$fileId
				, $userId
			);
			if (null === $file) continue;
			$item = new Item();
			$item->setId($file->getId());
			$item->setOwnerId($file->getOwner()->getUID());
			$item->setName($file->getName());
			$item->addRater(Util::toRater($userId, Util::toRating($createTs)));
		}
		$result->closeCursor();
		return $list;
	}

	/**
	 * This method deletes first a file from the database if it is already inserted.
	 * Then, the method calls a method to insert the file to the database.
	 *
	 * @param File $file
	 * @param string $userId
	 * @param string $type
	 *
	 * @since 1.0.0
	 */
	public function deleteBeforeInsert(File $file, string $userId, string $type) {
		$presentable = $this->isPresentable($file, $userId, $type);
		if ($presentable) {
			$this->deleteFile($file, $userId, $type);
		}
		$this->insertFile($file, $userId, $type);
	}

	/**
	 * checks whether a file is already inserted or not
	 *
	 * @param File $file file that should be checked
	 * @param string $userId the user id that has changed/tagged the file
	 * @param string $type the files type
	 *
	 * @return bool whether the file inserted or not
	 * @since 1.0.0
	 */
	public function isPresentable(File $file, string $userId, string $type) {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->select(DbConstants::TB_CFL_FILE_ID)
				->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
				->where($query->expr()->eq(DbConstants::TB_CFL_FILE_ID, $query->createNamedParameter($file->getId())))
				->andWhere($query->expr()->eq(DbConstants::TB_CFL_TYPE, $query->createNamedParameter($type)))
				->andWhere($query->expr()->eq(DbConstants::TB_CFL_USER_ID, $query->createNamedParameter($userId)));
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

	/**
	 * deletes a single file from the database
	 *
	 * @param File $file the file that should be deleted
	 * @param string $userId the user that made the change/tag
	 * @param string $type the type of changed files
	 *
	 * @return bool delete operation was successful or not
	 * @since 1.0.0
	 */
	public function deleteFile(File $file, string $userId, string $type) {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->delete(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
				->where($query->expr()->eq(DbConstants::TB_CFL_FILE_ID, $query->createNamedParameter($file->getId())))
				->andWhere($query->expr()->eq(DbConstants::TB_CFL_TYPE, $query->createNamedParameter($type)))
				->andWhere($query->expr()->eq(DbConstants::TB_CFL_USER_ID, $query->createNamedParameter($userId)))
				->execute();
			return true;
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			return false;
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
			return false;
		}
	}

	/**
	 * inserts information about a single file in the database
	 *
	 * @param File $file file whose information should be stored
	 * @param string $userId the user that has made the change/tag
	 *
	 * @return bool whether the insertation was successful or not
	 * @since 1.0.0
	 */
	public function insertFile(File $file, string $userId): bool {
		$query = $this->dbConnection->getQueryBuilder();
		try {
			$query->insert(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)->values(
				[
					DbConstants::TB_CFL_FILE_ID => $query->createNamedParameter($file->getId()),
					DbConstants::TB_CFL_CHANGE_TS => $query->createNamedParameter(time()),
					DbConstants::TB_CFL_CREATION_TS => $query->createNamedParameter(time()),
					DbConstants::TB_CFL_USER_ID => $query->createNamedParameter($userId),
					DbConstants::TB_CFL_TYPE => $query->createNamedParameter("file"),
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
	 * returns the last changed timestamp for a given file/type.
	 *
	 * @param File $file the file for that the query should be executed
	 * @param string $userId the user that has made the change/tag
	 * @param string $type the type of the query
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function queryChangeTs(File $file, string $userId, string $type): int {
		$query = $this->dbConnection->getQueryBuilder();
		$changeTs = 0;
		try {
			$query->select(DbConstants::TB_CFL_CHANGE_TS)
				->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
				->where($query->expr()->eq(DbConstants::TB_CFL_FILE_ID, $query->createNamedParameter($file->getId())))
				->andWhere($query->expr()->eq(DbConstants::TB_CFL_TYPE, $query->createNamedParameter($type)))
				->andWhere($query->expr()->eq(DbConstants::TB_CFL_USER_ID, $query->createNamedParameter($userId)));
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
}