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

namespace OCA\RecommendationAssistant\BackgroundJob;

use OC\BackgroundJob\TimedJob;
use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Objects\Logger;
use OCP\AppFramework\QueryException;

/**
 * Class that is the entry point of the background job registered in info.xml.
 * This class defines the interval and calls the necessary services.
 *
 * @package OCA\RecommendationAssistant\BackgroundJob
 * @since 1.0.0
 */
class RecommenderJob extends TimedJob {
	/**
	 * @const INTERVAL the interval in which the job should run.
	 * Actually every 5 hours.
	 */
	const INTERVAL = 5 * 60 * 60;

	/**
	 * Class constructor defines the interval in which the background job runs
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if (Application::DEBUG) {
			$this->setInterval(1);
		} else {
			$this->setInterval(RecommenderJob::INTERVAL);
		}
	}

	/**
	 * inherited method run ensures that the background job runs
	 *
	 * @param $argument argument passed to the job
	 * @since 1.0.0
	 */
	protected function run($argument) {
		try {
			$app = new Application();
			$container = $app->getContainer();
			$recommenderService = $container->query(Application::RECOMMENDER_JOB_NAME);
			$recommenderService->run();
		} catch (QueryException $exception) {
			Logger::error($exception->getMessage());
		}
	}
}




