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

use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\Recommendation;
use OCA\RecommendationAssistant\Service\UserService;
use OCP\IDBConnection;

/**
 * Class that servers as a interface to the datastorage for the recommendations.
 *
 * @package OCA\RecommendationAssistant\Db
 * @since   1.0.0
 */
class RecommendationManager {
	/**
	 * @var IDBConnection $dbConnection
	 */
	private $dbConnection = null;

	private $userService = null;

	/**
	 * Class constructor gets an instance of IDBConnection injected
	 *
	 * @param IDBConnection $dbConnection
	 *
	 * @param UserService $userService
	 * @since 1.0.0
	 */
	public function __construct(IDBConnection $dbConnection
		, UserService $userService) {
		$this->dbConnection = $dbConnection;
		$this->userService = $userService;
	}


	public function add(Recommendation $recommendation) {
		$query = $this->dbConnection->getQueryBuilder();
		$userId = $recommendation->getUserId();
		$stack = $recommendation->getRecommendations();
		while (!$stack->isEmpty()) {
			/** @var Item $item */
			$item = $stack->pop();
			if ($this->isRecommendedToUser($item, $userId)) continue;
			if (!$this->userService->hasAccess($userId, $item->getId())) continue;
			$query->insert(DbConstants::TABLE_NAME_RECOMMENDATIONS)->values(
				[
					DbConstants::TB_RC_FILE_ID => $query->createNamedParameter($item->getId()),
					DbConstants::TB_RC_USER_ID => $query->createNamedParameter($userId),
					DbConstants::TB_RC_OWNER_ID => $query->createNamedParameter($item->getOwnerId()),
					DbConstants::TB_RC_TRANSPARENCY_CODE => $query->createNamedParameter(0),
					DbConstants::TB_RC_RECOMMENDATION_SCORE => $query->createNamedParameter(0),
					DbConstants::TB_RC_CREATION_TS => $query->createNamedParameter((new \DateTime())->getTimestamp()),
				]
			);
		}
		ConsoleLogger::warn("set transparency code");
		ConsoleLogger::warn("set recommendation score");
	}

	/**
	 * This method checks if a given item is already recommended to an user
	 * by querying the database.
	 *
	 * @param Item $item
	 * @param string $user
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	private function isRecommendedToUser(Item $item, string $user) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->select(DbConstants::TB_RC_ID)
			->from(DbConstants::TABLE_NAME_RECOMMENDATIONS)
			->where($query->expr()->eq(DbConstants::TB_RC_FILE_ID, $query->createNamedParameter($item->getId())))
			->andWhere($query->expr()->eq(DbConstants::TB_RC_USER_ID, $query->createNamedParameter($user)));
		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();
		return $row !== false;
	}
}