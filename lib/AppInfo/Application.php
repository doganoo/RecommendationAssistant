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

use OCA\Files\Service\TagService;
use OCA\RecommendationAssistant\Db\ChangedFilesManager;
use OCA\RecommendationAssistant\Db\ProcessedFilesManager;
use OCA\RecommendationAssistant\Hook\FileHook;
use OCA\RecommendationAssistant\Log\Logger;
use OCP\AppFramework\App;
use OCP\AppFramework\QueryException;
use OCP\IContainer;
use OCP\Util;
use Symfony\Component\EventDispatcher\GenericEvent;

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
	 * @const DEBUG RecommendationAssistant is in Debug mode or not
	 *
	 * actual behaviour:
	 * <li>files are processed even if they are processed in the past</li>
	 * <li>RecommenderService interval equals to 1</li>
	 * <li>ConsoleLogger logs the messages to the console</li>
	 */
	const DEBUG = false;

	/**
	 * @const SHARED_INSTANCE_STORAGE the fully qualified class name of a SharedStorage instance
	 */
	const SHARED_INSTANCE_STORAGE = "\OCA\Files_Sharing\SharedStorage";

	/**
	 * @const RECOMMENDATION_ENTITIY_NAME the entity name for recommendations for the view
	 */
	const RECOMMENDATION_ENTITIY_NAME = "\OCA\RecommendationAssistant\Db\Entity\Recommendation";

	/**
	 * @const RECOMMENDATION_THRESHOLD defines the threshold that has to be exceeded in
	 * order to get recommended.
	 */
	const RECOMMENDATION_THRESHOLD = 1;

	/**
	 * @const K_NEAREST_NEIGHBOR_SIMILARITY_THRESHOLD the minimum similarity of
	 * two items that are necessary for rating prediction
	 */
	const K_NEAREST_NEIGHBOR_SIMILARITY_THRESHOLD = 0.2;

	/**
	 * @const RATING_WEIGHT_LAST_CHANGE the weight for the rating used in
	 * RecommenderService for the last modification timestamp rating
	 */
	const RATING_WEIGHT_LAST_CHANGE = 0.75;

	/**
	 * @const RATING_WEIGHT_LAST_FAVORITE the weight for the rating used in
	 * RecommenderService for the last favorite tagging timestamp
	 */
	const RATING_WEIGHT_LAST_FAVORITE = 0.25;

	/**
	 * @const COLLABORATIVE_FILTERING_WEIGHT weights for hybridization
	 */
	const COLLABORATIVE_FILTERING_WEIGHT = 0.75;

	/**
	 * Class constructor calls the parent constructor with APP_ID
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(self::APP_ID);
		$container = $this->getContainer();
		$server = $container->getServer();

		$container->registerService(FileHook::class, function (IContainer $c) use ($server) {
			return new FileHook(
				$server->getUserSession(),
				$server->getRequest(),
				$server->getRootFolder(),
				$c->query(ProcessedFilesManager::class),
				$c->query(ChangedFilesManager::class)
			);
		});
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

	public function register() {
		Util::connectHook('OC_Filesystem', 'read', $this, 'callFileHook');
		//TODO listen to postDelete hook and remove file from recommendations and already processed files!

		$this->getContainer()->getServer()->getEventDispatcher()->addListener(TagService::class . '::addFavorite', function (GenericEvent $event) {
			$userId = $event->getArgument('userId');
			$fileId = $event->getArgument('fileId');
			/** @var FileHook $hook */
			$hook = $this->getContainer()->query(FileHook::class);
			$hook->runFavorite($userId, $fileId, "addFavorite");
		});

		$this->getContainer()->getServer()->getEventDispatcher()->addListener(TagService::class . '::removeFavorite', function (GenericEvent $event) {
			$userId = $event->getArgument('userId');
			$fileId = $event->getArgument('fileId');
			/** @var FileHook $hook */
			$hook = $this->getContainer()->query(FileHook::class);
			$hook->runFavorite($userId, $fileId, "removeFavorite");
		});

		$this->getContainer()->getServer()->getEventDispatcher()->addListener(
			'OCA\Files::loadAdditionalScripts',
			function () {
				Util::addScript(Application::APP_ID, 'app');
				Util::addStyle(Application::APP_ID, 'style');
			}
		);
	}

	/**
	 * the file hook that is executed when a file is changed.
	 *
	 * @param $params
	 * @since 1.0.0
	 */
	public function callFileHook($params) {
		$container = $this->getContainer();
		$fileHookExecuted = false;
		try {
			/** @var FileHook $fileHook */
			$fileHook = $container->query(FileHook::class);
			if ($fileHook instanceof FileHook) {
				$fileHookExecuted = $fileHook->run($params);
			}

			if (!$fileHookExecuted) {
				Logger::warn("file hook is not executed");
			}
		} catch (QueryException $e) {
			Logger::error($e->getMessage());
		}
	}
}
