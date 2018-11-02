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

namespace OCA\RecommendationAssistant\Db\Mapper;

use OCA\RecommendationAssistant\AppInfo\Application;
use OCP\AppFramework\Db\Mapper;
use OCP\IDbConnection;

/**
 * RecommendationMapper class is the mapper class for the RecommendationController.
 * This class executes SQL queries in order to get the data from the database.
 *
 * @package OCA\RecommendationAssistant\Db\Mapper
 * @since   1.0.0
 */
class RecommendationMapper extends Mapper {
	/**
	 * Class constructor gets IDBConnection instance injected
	 *
	 * @param IDBConnection $db the db connection to query the database
	 *
	 * @since 1.0.0
	 */
	public function __construct(IDbConnection $db) {
		parent::__construct($db, Application::APP_ID, Application::RECOMMENDATION_ENTITIY_NAME);
	}

	/**
	 * @param $userId
	 *
	 * @return array
	 * @return array a list of entities
	 * @since 1.0.0
	 */
	public function findAll($userId) {
		//TODO order by recommendation score and insert date
		$sql = 'SELECT file_id AS fileId, transparency_code FROM *PREFIX*recommendations WHERE user_id = ? ORDER BY creation_ts desc LIMIT 3;';
//		$sql = 'SELECT file_id AS fileId, transparency_code FROM *PREFIX*recommendations WHERE user_id = ? ORDER BY recommendation_score desc LIMIT 3;';
		$entities = $this->findEntities($sql, [$userId]);
		return $entities;
	}
}