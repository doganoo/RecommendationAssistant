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

namespace OCA\RecommendationAssistant\Util;


class FileUtil {

	public static function getNode(string $fileId, string $userId) {
		/** @var \OCP\Files\Folder $rootFolder */
		$userFolder = \OC::$server->getRootFolder()->getUserFolder($userId);
		/** @var \OCP\Files\Node[] $nodeArray */
		$nodeArray = $userFolder->getById($fileId);
		$return = empty($nodeArray[0]) ? null : $nodeArray[0];
		return $return;
	}

}