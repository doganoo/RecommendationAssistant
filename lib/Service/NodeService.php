<?php
/**
 * @copyright Copyright (c) 2018, Dogan Ucar (dogan@dogan-ucar.de)
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


namespace OCA\RecommendationAssistant\Service;


use OCA\RecommendationAssistant\Log\Logger;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\IUserSession;

/**
 * Class NodeService
 *
 * @package OCA\RecommendationAssistant\Service
 */
class NodeService {
	/** @var IUserSession $userSession */
	private $userSession = null;
	/** @var IRootFolder $rootFolder */
	private $rootFolder = null;

	/**
	 * NodeService constructor.
	 *
	 * @param IUserSession $userSession
	 * @param IRootFolder $rootFolder
	 */
	public function __construct(
		IUserSession $userSession
		, IRootFolder $rootFolder
	) {
		$this->userSession = $userSession;
		$this->rootFolder = $rootFolder;
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
	 *
	 * TODO make none static
	 */
	public static function getFile(string $nodeId, string $userId) {
		/** @var \OCP\Files\Folder $rootFolder */
		$userFolder = \OC::$server->getRootFolder()->getUserFolder($userId);
		/** @var \OCP\Files\Node[] $nodeArray */
		$nodeArray = $userFolder->getById($nodeId);
		/** @var Node $return */
		$return = empty($nodeArray[0]) ? null : $nodeArray[0];
		if ($return !== null && $return instanceof File) {
			/** @var File $return */
			return $return;
		} else {
			return null;
		}
	}

	/**
	 * returns the Node instance that correspondents to $path
	 *
	 * @param string $path the path
	 *
	 * @return null|Node the node that is requested or null, if an error occures
	 * @since 1.0.0
	 */
	public function getNode($path) {
		$node = null;
		try {
			$currentUserId = $this->userSession->getUser()->getUID();
			$userFolder = $this->rootFolder->getUserFolder($currentUserId);
			$node = $userFolder->get($path);
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
		}
		return $node;
	}
}