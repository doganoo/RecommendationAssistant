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

namespace OCA\RecommendationAssistant\AppInfo;

use OCP\AppFramework\App;

/**
 * All controller instances that are registered by IContainer::registerService
 * must inherit this class.
 * Actually, this class serves as a collection of constants and static functions
 * in order to collect them centrally.
 *
 * @package OCA\RecommendationAssistant\AppInfo
 * @since 1.0.0
 */
class Application extends App {
	/**
	 * @const APP_ID the unique application id
	 */
	const APP_ID = "recommendation_assistant";

	/**
	 * @const APP_NAME the application name
	 */
	const APP_NAME = "RecommendationAssistant";

	/**
	 * @const RECOMMENDER_JOB_NAME the service class that performs recommendations
	 */
	const RECOMMENDER_JOB_NAME = "OCA\RecommendationAssistant\Service\RecommenderService";

	/**
	 * @const USER_PROFILE_JOB_NAME the service class that assembles keywords for the users profile
	 */
	const USER_PROFILE_JOB_NAME = "OCA\RecommendationAssistant\Service\UserProfileService";

	/**
	 * @const SHARED_INSTANCE_STORAGE the fully qualified class name of a SharedStorage instance
	 */
	const SHARED_INSTANCE_STORAGE = "\OCA\Files_Sharing\SharedStorage";

	/**
	 * @const DEBUG RecommendationAssistant is in Debug mode or not
	 *
	 * actual behaviour:
	 * <li>files are processed even if they are processed in the past</li>
	 * <li>RecommenderService interval equals to 1</li>
	 * <li>UserProfileService interval equals to 1</li>
	 * <li>ConsoleLogger logs the messages to the console</li>
	 */
	const DEBUG = true;

	/**
	 * @const HOOK_FILE_HOOK_NAME the file hook name
	 */
	const HOOK_FILE_HOOK_NAME = "OCA\RecommendationAssistant\Hook\FileHook";

	/**
	 * @const RECOMMENDATION_ENTITIY_NAME the entity name for recommendations for the view
	 */
	const RECOMMENDATION_ENTITIY_NAME = "\OCA\RecommendationAssistant\Db\Entity\Recommendation";

	/**
	 * @const RECOMMENDATION_THRESHOLD defines the threshold that has to be exceeded in
	 * order to get recommended.
	 */
	const RECOMMENDATION_THRESHOLD = 0.5;

	/**
	 * @const KEYWORD_REMOVAL_DAYS number of days after that keywords of an user
	 * profile is deleted
	 */
	const KEYWORD_REMOVAL_DAYS = 180;

	/**
	 * @const K_NEAREST_NEIGHBOR_SIMILARITY_THRESHOLD the minimum similarity of
	 * two items that are necessary for rating prediction
	 */
	const K_NEAREST_NEIGHBOR_SIMILARITY_THRESHOLD = 0.5;

	/**
	 * @const STOPWORD_REMOVAL_PERCENTAGE the percentage of removing stopwords
	 */
	const STOPWORD_REMOVAL_PERCENTAGE = 0.33;

	/**
	 * @const RATING_WEIGHT_LAST_CHANGE the weight for the rating used in
	 * RecommenderService for the last modification timestamp rating
	 */
	const RATING_WEIGHT_LAST_CHANGE = 1;

	/**
	 * @const RATING_WEIGHT_LAST_FAVORITE the weight for the rating used in
	 * RecommenderService for the last favorite tagging timestamp
	 */
	const RATING_WEIGHT_LAST_FAVORITE = 0;

	/**
	 * Class constructor calls the parent constructor with APP_ID
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(Application::APP_ID);
	}

	/**
	 * static class that returns the data directory
	 *
	 * @return string $dataDir the full path to the data/ directory
	 * @since 1.0.0
	 */
	public static function getDataDirectory(): string {
		return $dataDir = \OC::$server->getConfig()->getSystemValue('datadirectory', '');
	}
}
