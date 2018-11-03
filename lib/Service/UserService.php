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


namespace OCA\RecommendationAssistant\Service;


use OCP\Files\IRootFolder;
use OCP\IUserManager;

/**
 * Class UserService
 *
 * @package OCA\RecommendationAssistant\Service
 */
class UserService {
	/** @var null|IRootFolder $rootFolder */
	private $rootFolder = null;

	/**
	 * UserService constructor.
	 *
	 * @param IUserManager $userManager
	 * @param IRootFolder $rootFolder
	 */
	public function __construct(IUserManager $userManager, IRootFolder $rootFolder) {
		$this->rootFolder = $rootFolder;

	}

	/**
	 * @param $uid
	 * @param $fileId
	 * @return bool
	 */
	public function hasAccess($uid, $fileId): bool {
		$nodes = $this->rootFolder->getUserFolder($uid)->getById($fileId);
		NodeService::getFile($fileId, $uid);
		return null !== $nodes && \count($nodes) >= 1;
	}

}