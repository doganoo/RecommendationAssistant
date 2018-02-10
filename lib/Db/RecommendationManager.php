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
use OCA\RecommendationAssistant\Objects\HybridItem;
use OCA\RecommendationAssistant\Objects\HybridList;
use OCA\RecommendationAssistant\Objects\Item;
use OCP\IDBConnection;
use OCP\IUser;

/**
 * Class that servers as a interface to the datastorage for the recommendations.
 *
 * @package OCA\RecommendationAssistant\Db
 * @since 1.0.0
 */
class RecommendationManager {
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
	 * inserts a HybridItem that represents a recommendation
	 *
	 * @param HybridItem $hybridItem the item containing a recommendation
	 * @return bool whether the insert operation was successful or not
	 * @since 1.0.0
	 */
	private function insertHybrid(HybridItem $hybridItem): bool {
		if ($this->isRecommendedToUser($hybridItem->getItem(), $hybridItem->getUser())) {
			return true;
		}
		$query = $this->dbConnection->getQueryBuilder();
		$query->insert(DbConstants::TABLE_NAME_RECOMMENDATIONS)->values(
			[
				DbConstants::TB_RC_FILE_ID => $query->createNamedParameter($hybridItem->getItem()->getId()),
				DbConstants::TB_RC_USER_ID => $query->createNamedParameter(($hybridItem->getUser()->getUID())),
				DbConstants::TB_RC_OWNER_ID => $query->createNamedParameter($hybridItem->getItem()->getOwner()->getUID()),
				DbConstants::TB_RC_CREATION_TS => $query->createNamedParameter((new \DateTime())->getTimestamp())
			]
		);

		try {
			$query->execute();
		} catch (\Exception $exception) {
			ConsoleLogger::debug($exception->getMessage());
			return false;
		}
		$lastInsertId = $query->getLastInsertId();
		return is_int($lastInsertId);
	}

	/**
	 * inserts multiple instances of HybridItem.
	 * This method calls the insertHybrid() method of this class.
	 *
	 * @param HybridList $hybridList the list with all items
	 * @return int $i number of items inserted
	 * @since 1.0.0
	 */
	public function insertHybridList(HybridList $hybridList) {
		$i = 0;
		foreach ($hybridList as $userId => $array) {
			/** @var HybridItem $hybrid */
			foreach ($array as $itemId => $hybrid) {
				$this->insertHybrid($hybrid);
				$i++;
			}
		}
		return $i;
	}

	public function isRecommendedToUser(Item $item, IUser $user) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->select(DbConstants::TB_RC_ID)
			->from(DbConstants::TABLE_NAME_RECOMMENDATIONS)
			->where($query->expr()->eq(DbConstants::TB_RC_FILE_ID, $query->createNamedParameter($item->getId())))
			->andWhere($query->expr()->eq(DbConstants::TB_RC_USER_ID, $query->createNamedParameter($user->getUID())));

		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();
		return $row !== false;
	}

}