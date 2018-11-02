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
		$limit = 90 * 60 * 60 * 24;
		$now = \time() - $limit;
		$query = $this->dbConnection->getQueryBuilder();
		$query->delete(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
			->where($query->expr()->lt(DbConstants::TB_CFL_CREATION_TS, $query->createNamedParameter($now - $limit)));
		$rowCount = $query->execute();
		ConsoleLogger::debug("deleted rows: $rowCount");
	}

	/**
	 * @return ArrayList
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 *
	 */
	public function allFiles(): ArrayList {
		$list = new ArrayList();
		$query = $this->dbConnection->getQueryBuilder();
		$query->select(
			DbConstants::TB_CFL_FILE_ID
			, DbConstants::TB_CFL_USER_ID
			, DbConstants::TB_CFL_CREATION_TS
		)->from(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
			->orderBy(DbConstants::TB_CFL_CREATION_TS, "DESC")
			->setMaxResults(500);
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
			$list->add($item);
		}
		$result->closeCursor();
		ConsoleLogger::debug("selected rows: {$result->rowCount()}");
		return $list;
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

	public function deleteList(ArrayList $list): bool {
		$deleted = true;
		foreach ($list as $id) {
			$deleted &= $this->deleteById($id);
		}
		return $deleted;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteById(int $id): bool {
		$query = $this->dbConnection->getQueryBuilder();
		$query->delete(DbConstants::TABLE_NAME_CHANGED_FILES_LOG)
			->where($query->expr()->lt(DbConstants::TB_CFL_ID, $query->createNamedParameter($id)));
		$rowCount = $query->execute();
		return 0 !== $rowCount;
	}

}