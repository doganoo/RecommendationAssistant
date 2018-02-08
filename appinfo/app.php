<?php
/**
 *
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

use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Exception\AutoloaderNotFoundException;
use OCP\Util;

/**
 * $l for translation
 */
$l = \OC::$server->getL10N(Application::APP_ID);

/**
 * to add a script whenever the page refreshes
 */
$eventDispatcher = \OC::$server->getEventDispatcher();
$eventDispatcher->addListener(
	'OCA\Files::loadAdditionalScripts',
	function () {
		Util::addscript(\OCA\RecommendationAssistant\AppInfo\Application::APP_ID, 'app');
		Util::addStyle(\OCA\RecommendationAssistant\AppInfo\Application::APP_ID, "style");
	}
);

/**
 * RecommendationAssistant uses external libraries in order
 * to work properly. The app should not begin to work if
 * the autoload.php composer file is missing.
 */
if ((@include_once __DIR__ . '/../vendor/autoload.php') === false) {
	/**
	 * AutoloaderNotFoundException is a custom exception defined by RecommendationAssistant
	 */
	throw new AutoloaderNotFoundException ($l->t("Could not find autoload.php. Have you installed all composer dependencies?"));
}

$app = new Application();