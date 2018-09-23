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

namespace OCA\RecommendationAssistant\Util;

use OCP\Files\File;
use OCP\Files\Node;

/**
 * NodeUtil is a utility class for all \OCP\Files\Node objects. It defines
 * all helper methods that are relevant for nodes. This class is not instantiable
 * because all methods are static.
 *
 * @package OCA\RecommendationAssistant\Util
 * @since   1.0.0
 */
class NodeUtil{
	/**
	 * class constructor is private because all methods are public static.
	 *
	 * @since 1.0.0
	 */
	private function __construct(){
	}

	/**
	 * creates a \OCP\Files\File instance for a given node id. The user
	 * id is necessary for querying the root folder.
	 *
	 * @param string $nodeId
	 * @param string $userId
	 *
	 * @return null|File
	 * @since 1.0.0
	 */
	public static function getFile(string $nodeId, string $userId){
		/** @var \OCP\Files\Folder $rootFolder */
		$userFolder = \OC::$server->getRootFolder()->getUserFolder($userId);
		/** @var \OCP\Files\Node[] $nodeArray */
		$nodeArray = $userFolder->getById($nodeId);
		/** @var Node $return */
		$return = empty($nodeArray[0]) ? null : $nodeArray[0];
		if($return !== null && $return instanceof File){
			/** @var File $return */
			return $return;
		} else{
			return null;
		}
	}
}