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
use OCA\RecommendationAssistant\Service\UserProfileService;

/**
 * Class that is the entry point of the background job registered in info.xml.
 * This class defines the interval and calls the necessary services.
 *
 * @package OCA\RecommendationAssistant\BackgroundJob
 * @since 1.0.0
 */
class UserProfileJob extends TimedJob {
	/**
	 * @const INTERVAL the interval in which the job should run.
	 * Actually once a day.
	 */
	const INTERVAL = 1000 * 60 * 60 * 24;

	/**
	 * @var UserProfileService $userProfileService
	 */
	private $userProfileService = null;

	/**
	 * Class constructor defines the interval in which the background job runs
	 *
	 * @param UserProfileService $userProfileService
	 * @since 1.0.0
	 */
	public function __construct(UserProfileService $userProfileService) {
		if (Application::DEBUG) {
			$this->setInterval(1);
		} else {
			$this->setInterval(UserProfileJob::INTERVAL);
		}
		$this->userProfileService = $userProfileService;
	}

	/**
	 * inherited method run ensures that the background job runs
	 *
	 * @param string $argument passed to the job
	 * @since 1.0.0
	 */
	protected function run($argument) {
		$this->userProfileService->run();
	}
}