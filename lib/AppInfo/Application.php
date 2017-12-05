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
	public const APP_ID = "recommendation_assistant";

	/**
	 * @const APP_NAME the application name
	 */
	public const APP_NAME = "RecommenderJob";

	/**
	 * @const RECOMMENDER_JOB_NAME the service class that performs recommendations
	 */
	public const RECOMMENDER_JOB_NAME = "OCA\RecommendationAssistant\Service\RecommenderService";

	/**
	 * @const USER_PROFILE_JOB_NAME the service class that assembles keywords for the users profile
	 */
	public const USER_PROFILE_JOB_NAME = "OCA\RecommendationAssistant\Service\UserProfileService";

	/**
	 * @const SHARED_INSTANCE_STORAGE the fully qualified class name of a SharedStorage instance
	 */
	public const SHARED_INSTANCE_STORAGE = "\OCA\Files_Sharing\SharedStorage";

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
