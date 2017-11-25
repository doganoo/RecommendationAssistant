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

class Application extends App {
	public const APPID = "recommendation_assistant";
	public const APPNAME = "RecommenderJob";
	public const RECOMMENDER_JOB_NAME = "OCA\RecommendationAssistant\Service\RecommenderService";
	public const SHARED_INSTANCE_STORAGE = "\OCA\Files_Sharing\SharedStorage";

	public function __construct() {
		parent::__construct(Application::APPID);
	}
}
