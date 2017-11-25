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

namespace OCA\RecommendationAssistant\Objects;


use OCA\RecommendationAssistant\AppInfo\Application;

/**
 * Collection of static methods that are used to log into the Nextcloud log files
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class Logger {
	/**
	 * Logs a message into the Nextcloud log file with log level debug and the
	 * appname that is defined in the Application class.
	 *
	 * @param string $message the message that should be logged
	 * @since 1.0.0
	 */
	public static function debug($message) {
		$logger = \OC::$server->getLogger();
		$logger->debug($message, ["app" => Application::APPNAME]);
	}
}