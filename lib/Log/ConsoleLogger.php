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

namespace OCA\RecommendationAssistant\Log;


use OCA\RecommendationAssistant\AppInfo\Application;
use OCP\Util;

/**
 * Collection of static methods that are used to log to the shell
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class ConsoleLogger {
	/**
	 * Logs a message to the console with log level debug and the
	 * appname that is defined in the Application class.
	 *
	 * @param string $message the message that should be logged
	 * @since 1.0.0
	 */
	public static function debug($message) {
		ConsoleLogger::log(Util::DEBUG, $message);
	}

	/**
	 * Logs a message to the console with log level warn and the
	 * appname that is defined in the Application class.
	 *
	 * @param string $message the message that should be logged
	 * @since 1.0.0
	 */
	public static function warn($message) {
		ConsoleLogger::log(Util::WARN, $message);
	}

	/**
	 * Logs a message to the console with log level error and the
	 * appname that is defined in the Application class.
	 *
	 * @param string $message the message that should be logged
	 * @since 1.0.0
	 */
	public static function error($message) {
		ConsoleLogger::log(Util::ERROR, $message);
	}

	/**
	 * logs the message and level to the shell console only if DEBUG = true
	 * in Application.php and the log level of the NC config is set to the
	 * corresponding level.
	 *
	 * @param int $level
	 * @param string $message
	 */
	private static function log(int $level, string $message) {
		$config = \OC::$server->getSystemConfig();
		$minLevel = min($config->getValue('loglevel', Util::WARN), Util::FATAL);
		if (Application::DEBUG && $level >= $minLevel) {
			$date = new \DateTime();
			echo $date->format("Y-m-d H:i:s");
			echo ": ";
			echo $message;
			echo "\n";
		}
	}
}