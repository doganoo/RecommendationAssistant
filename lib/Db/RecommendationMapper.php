<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 14.01.18
 * Time: 13:37
 */

namespace OCA\RecommendationAssistant\Db;


use OCA\RecommendationAssistant\AppInfo\Application;
use OCP\AppFramework\Db\Mapper;
use OCP\IDbConnection;


class RecommendationMapper extends Mapper {

	public function __construct(IDbConnection $db) {
		parent::__construct($db, Application::APP_ID, '\OCA\RecommendationAssistant\Db\Recommendation');
	}

	public function findAll($userId) {
		$sql = 'SELECT fileId, "" as fileName FROM *PREFIX*recommendations WHERE user_id = ? LIMIT 3;';
		return $this->findEntities($sql, [$userId]);
	}

}