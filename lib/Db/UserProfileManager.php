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

use OCA\RecommendationAssistant\Objects\KeywordList;
use OCA\RecommendationAssistant\Objects\Keyword;
use OCP\IDBConnection;
use OCP\IUser;

/**
 * Class that servers as a interface to the datastorage about the keywords that
 * are assembled for an user
 *
 * @package OCA\RecommendationAssistant\Db
 * @since 1.0.0
 */
class UserProfileManager {
	/**
	 * @var IDBConnection $dbConnection
	 */
	private $dbConnection = null;

	/**
	 * @const TABLE_NAME the name of the database table that is used in this
	 * class
	 */
	const TABLE_NAME = "user_profile";

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
	 * inserts a keyword, its TFIDF value and the user id into the database
	 *
	 * @param Keyword $item the keyword and its TFIDF value
	 * @param IUser $user the user who is associated to the item
	 * @return bool whether the insertation was successfull or not
	 * @since 1.0.0
	 */
	private function insertKeyword(Keyword $item, IUser $user): bool {
		$query = $this->dbConnection->getQueryBuilder();
		$query->insert(UserProfileManager::TABLE_NAME)->values(
			[
				"user_id" => $query->createNamedParameter($user->getUID()),
				"keyword" => $query->createNamedParameter($item->getKeyword()),
				"tfidf_value" => $query->createNamedParameter($item->getTfIdf())
			]
		);
		$query->execute();
		$lastInsertId = $query->getLastInsertId();
		return is_int($lastInsertId);
	}

	/**
	 * inserts multiple keywords, its TFIDF values and the user id into the
	 * database. This method calls the insertKeyword() method of this class.
	 * Before insertation all keywords are deleted in order to ensure that
	 * no keywords are present twice in the database.
	 *
	 * @param KeywordList $keywordList the list that is inserted
	 * @param IUser $user the user who is associated to the item
	 * @since 1.0.0
	 */
	public function insertKeywords(KeywordList $keywordList, IUser $user) {
		$this->deleteForUser($user);
		foreach ($keywordList as $keywordItem) {
			$this->insertKeyword($keywordItem, $user);
		}
	}


	/**
	 * deletes all keywords for a given user
	 *
	 * @param IUser $user the user whose keywords should be deleted
	 * @since 1.0.0
	 */
	private function deleteForUser(IUser $user) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->delete("user_profile")
			->where($query->expr()->eq('user_id', $query->createNamedParameter($user->getUID())))
			->execute();
	}

	/**
	 * returns an instance of KeywordList that represents a list of keywords
	 * that are associated with a user and stored in the database.
	 *
	 * @param IUser $user the user whose keywords are requested
	 * @return KeywordList $keywordList the list with the keywords
	 * @since 1.0.0
	 */
	public function getKeywordListByUser(IUser $user) {
		$keywordList = new KeywordList();
		$query = $this->dbConnection->getQueryBuilder();
		$query->select('keyword', "tfidf_value")
			->from('user_profile', 'r')
			->where($query->expr()->eq('user_id', $query->createNamedParameter($user->getUID())));

		$result = $query->execute();
		while (false !== $row = $result->fetch()) {
			$keyword = new Keyword();
			$keyword->setKeyword($row["keyword"]);
			$keyword->setTfIdf($row["tfidf_value"]);
			$keywordList->add($keyword);
		}
		$result->closeCursor();
		return $keywordList;
	}
}