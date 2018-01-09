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

use OCP\IDBConnection;

/**
 * Class that servers as a interface to the datastorage that contains
 * the weights that specify the similarity between user groups.
 *
 * @package OCA\RecommendationAssistant\Db
 * @since 1.0.0
 */
class GroupWeightsManager {
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
	private function deleteForGroup(string $groupId) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->delete(DbConstants::TABLE_NAME_GROUP_WEIGHTS)
			->where($query->expr()->eq(DbConstants::TB_GW_SOURCE_GROUP_ID, $query->createNamedParameter($groupId)))
			->execute();
	}

	/**
	 * returns the weight for two given groups. This method returns the weight of
	 * 1 if there is no weight available in the database yet. The value 1 means
	 * that the groups are similar.
	 *
	 * @param string $sourceGroupId the source group id for that the weight is requested
	 * @param string $targetGroupId the target group id for that the weight is requested
	 * @return float the weight
	 * @since 1.0.0
	 */
	public function getGroupWeightForGroups(string $sourceGroupId, string $targetGroupId): float {
		$weight = 1;
		$query = $this->dbConnection->getQueryBuilder();
		$query->select(DbConstants::TB_GW_VALUE)
			->from(DbConstants::TABLE_NAME_GROUP_WEIGHTS)
			->where($query->expr()->eq(DbConstants::TB_GW_SOURCE_GROUP_ID, $query->createNamedParameter($sourceGroupId)))
			->andWhere($query->expr()->eq(DbConstants::TB_GW_TARGET_GROUP_ID, $query->createNamedParameter($targetGroupId)));

		$result = $query->execute();
		while (false !== $row = $result->fetch()) {
			$weight = $row[DbConstants::TB_GW_VALUE];
		}
		$result->closeCursor();
		return floatval($weight);
	}
}