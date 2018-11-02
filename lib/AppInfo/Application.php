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

namespace OCA\RecommendationAssistant\AppInfo;

use doganoo\PHPUtil\Util\ClassUtil;
use OCA\RecommendationAssistant\Db\ChangedFilesManager;
use OCA\RecommendationAssistant\Hook\FileHook;
use OCA\RecommendationAssistant\Log\Logger;
use OCP\AppFramework\App;
use OCP\AppFramework\QueryException;
use OCP\IContainer;
use OCP\Util;

/**
 * All controller instances that are registered by IContainer::registerService
 * must inherit this class.
 * Actually, this class serves as a collection of constants and static functions
 * in order to collect them centrally.
 *
 * @package OCA\RecommendationAssistant\AppInfo
 * @since   1.0.0
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
	 * @const RECOMMENDATION_ENTITIY_NAME the entity name for recommendations for the view
	 */
	const RECOMMENDATION_ENTITIY_NAME = "\OCA\RecommendationAssistant\Db\Entity\Recommendation";
	/**
	 * @const RECOMMENDATION_THRESHOLD defines the threshold that has to be exceeded in
	 * order to get recommended.
	 */
	const RECOMMENDATION_THRESHOLD = 1;

	const SERIALIZATION_FILE_NAME = "serialize.txt";

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
				$c->query(ChangedFilesManager::class)
			);
		});
	}

	/**
	 * registers hooks
	 */
	public function register() {
		Util::connectHook('OC_Filesystem', 'read', $this, 'callFileHook');
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
	 *
	 * @since 1.0.0
	 */
	public function callFileHook($params) {
		Logger::debug("callFileHook start");
		$container = $this->getContainer();
		$fileHookExecuted = false;
		try {
			/** @var FileHook $fileHook */
			$fileHook = $container->query(FileHook::class);
			if ($fileHook instanceof FileHook) {
				$fileHookExecuted = $fileHook->run($params);
			} else {
				$className = (ClassUtil::getClassName($fileHook));
				Logger::debug("file hook instance is not correct: $className");
			}
			if (!$fileHookExecuted) {
				Logger::warn("file hook is not executed");
			}
		} catch (QueryException $e) {
			Logger::error($e->getMessage());
		} catch (\ReflectionException $e) {
			Logger::error($e->getMessage());
		}
		Logger::debug("callFileHook end");
	}
}
